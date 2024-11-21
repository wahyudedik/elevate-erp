<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\CompanyUser;
use App\Events\NewChatMessage;
use Filament\Facades\Filament;
use App\Models\ChatMessageRead;
use Laravel\Reverb\Loggers\Log;
use Illuminate\Support\Facades\Auth;

class Chat extends Component
{
    public $selectedRoom = null;
    public $message = '';
    public $searchQuery = '';
    public $showUserSelector = false;
    public $rooms = [];
    protected $listeners = ['echo-private:chat.{roomId},NewChatMessage' => '$refresh'];


    public function mount()
    {
        $this->loadChatRooms();
    }

    public function loadChatRooms()
    {
        $this->rooms = ChatRoom::whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        })->with(['lastMessage', 'users'])->get();
    }

    public function render()
    {
        $rooms = ChatRoom::whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        })->with(['lastMessage', 'users'])
            ->when($this->searchQuery, function ($query) {
                $query->where('name', 'like', '%' . $this->searchQuery . '%');
            })->get();

        $companyUsers = CompanyUser::where('company_id', Filament::getTenant()->id)
            ->whereHas('user', function ($query) {
                $query->where('id', '!=', Auth::id());
            })
            ->when($this->searchQuery, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->with('user')
            ->get();

        $messages = [];
        if ($this->selectedRoom) {
            $messages = ChatMessage::where('chat_room_id', $this->selectedRoom)
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();

            // Mark messages as read
            $this->markMessagesAsRead();
        }

        return view('livewire.chat', [
            'rooms' => $rooms,
            'messages' => $messages,
            'companyUsers' => $companyUsers,
            'unreadCount' => $this->getUnreadCount()
        ]);
    }

    public function markMessagesAsRead()
    {
        $messages = ChatMessage::where('chat_room_id', $this->selectedRoom)
            ->where('user_id', '!=', Auth::id())
            ->get();

        foreach ($messages as $message) {
            ChatMessageRead::updateOrCreate(
                [
                    'chat_message_id' => $message->id,
                    'user_id' => Auth::id(),
                ],
                [
                    'company_id' => Filament::getTenant()->id,
                    'read_at' => now()
                ]
            );
        }
    }


    public function getUnreadCount()
    {
        return ChatMessage::whereIn('chat_room_id', function ($query) {
            $query->select('chat_room_id')
                ->from('chat_room_users')
                ->where('user_id', Auth::id());
        })
            ->where('user_id', '!=', Auth::id())
            ->whereNotExists(function ($query) {
                $query->select('id')
                    ->from('chat_message_reads')
                    ->whereColumn('chat_message_id', 'chat_messages.id')
                    ->where('user_id', Auth::id());
            })
            ->count();
    }


    public function sendMessage()
    {
        if (empty(trim($this->message)) || !$this->selectedRoom) {
            return;
        }

        $message = ChatMessage::create([
            'company_id' => Filament::getTenant()->id,
            'chat_room_id' => $this->selectedRoom,
            'user_id' => Auth::id(),
            'message' => $this->message
        ]);

        // Add logging
        Log::info('Broadcasting message');

        broadcast(new NewChatMessage($message))->toOthers();

        $this->message = '';
        $this->dispatch('messageReceived');
    }

    public function startChat($userId)
    {
        $existingRoom = ChatRoom::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', Auth::id());
        })->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('type', 'private')->first();

        if (!$existingRoom) {
            $user = User::find($userId);
            $room = ChatRoom::create([
                'company_id' => Filament::getTenant()->id,
                'name' => $user->name,
                'type' => 'private'
            ]);

            $room->users()->attach([
                Auth::id() => ['company_id' => Filament::getTenant()->id],
                $userId => ['company_id' => Filament::getTenant()->id]
            ]);

            $this->selectedRoom = $room->id;
        } else {
            $this->selectedRoom = $existingRoom->id;
        }

        $this->showUserSelector = false;
        $this->loadChatRooms();
    }

    public function selectRoom($roomId)
    {
        $this->selectedRoom = $roomId;
        $this->markMessagesAsRead();
    }

    public function toggleUserSelector()
    {
        $this->showUserSelector = !$this->showUserSelector;
    }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->selectedRoom},NewChatMessage" => '$refresh',
            'messageReceived' => '$refresh'
        ];
    }
    public function refreshMessages()
    {
        // This will trigger a re-render with fresh messages
        $this->loadChatRooms();
    }

    public function deleteRoom($roomId)
    {
        $room = ChatRoom::find($roomId);

        if ($room) {
            // Delete all messages in the room
            ChatMessage::where('chat_room_id', $roomId)->delete();

            // Delete all user associations
            $room->users()->detach();

            // Delete the room
            $room->delete();

            // Reset selected room if it was the deleted one
            if ($this->selectedRoom === $roomId) {
                $this->selectedRoom = null;
            }

            // Refresh rooms list
            $this->loadChatRooms();
        }
    }
}
