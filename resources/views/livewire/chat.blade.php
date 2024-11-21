<div>
    @script
        Echo.private('chat.' + @js($selectedRoom))
        .listen('NewChatMessage', (e) => {
        $wire.$refresh();
        });
    @endscript

    <div class="container mx-auto">
        <div class="flex h-screen bg-gray-50">
            <!-- Sidebar -->
            <div class="w-1/3 border-r bg-white shadow-lg">
                <!-- Search and New Chat -->
                <div class="p-4 border-b">
                    <div class="flex gap-3 mb-2">
                        <input wire:model.live="searchQuery" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                            placeholder="Search conversations...">
                        <button wire:click="toggleUserSelector"
                            class="bg-blue-600 text-black px-5 py-2.5 rounded-lg hover:bg-blue-700 transition-all shadow-sm">
                            New Chat
                        </button>
                    </div>
                </div>

                <!-- Chat List -->
                <div class="overflow-y-auto h-[calc(100vh-120px)]">
                    @foreach ($rooms as $room)
                        <div wire:click="selectRoom({{ $room->id }})"
                            class="flex items-center p-4 hover:bg-gray-50 cursor-pointer border-b transition-all {{ $selectedRoom === $room->id ? 'bg-blue-50 border-l-4 border-l-blue-600' : '' }}">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold shadow-md">
                                {{ $room->type === 'group' ? 'G' : substr($room->name, 0, 1) }}
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-sm font-semibold text-gray-800">{{ $room->name }}</h3>
                                    @if ($room->unread_count > 0)
                                        <span class="bg-blue-600 text-white text-xs font-medium rounded-full px-2.5 py-1.5 shadow-sm">
                                            {{ $room->unread_count }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 truncate mt-1">
                                    {{ $room->lastMessage?->message ?? 'No messages yet' }}
                                </p>
                            </div>
                            <button wire:click.stop="deleteRoom({{ $room->id }})" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Chat Area -->
            <div class="w-full flex flex-col bg-white">
                @if ($selectedRoom)
                    <!-- Chat Header -->
                    <div class="bg-white p-4 flex items-center justify-between border-b shadow-sm">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold shadow-md">
                                {{ substr($rooms->find($selectedRoom)->name, 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-semibold text-gray-800">{{ $rooms->find($selectedRoom)->name }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ $rooms->find($selectedRoom)->users->count() }} members
                                </p>
                            </div>
                        </div>
                        <button wire:click="deleteRoom({{ $selectedRoom }})" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 bg-gray-50" id="messages-container">
                        @foreach ($messages as $message)
                            <div class="mb-6 {{ $message->user_id === Auth::id() ? 'text-right' : 'text-left' }}">
                                <div class="inline-block">
                                    @if ($message->user_id !== Auth::id())
                                        <p class="text-sm text-gray-600 mb-1 font-medium">{{ $message->user->name }}</p>
                                    @endif
                                    <div
                                        class="{{ $message->user_id === Auth::id() ? 'bg-blue-600 text-black' : 'bg-white shadow-md' }} rounded-2xl px-6 py-3 max-w-sm inline-block">
                                        <p class="text-sm">{{ $message->message }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ $message->created_at->format('H:i') }}
                                        @if ($message->user_id === Auth::id())
                                            @if ($message->read_at)
                                                <span class="text-blue-500 ml-1">✓✓</span>
                                            @else
                                                <span class="ml-1">✓</span>
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Input Area -->
                    <div class="bg-white p-4 border-t shadow-lg">
                        <div class="flex items-center gap-3">
                            <input type="text" wire:model="message" wire:keydown.enter="sendMessage"
                                class="flex-1 border border-gray-200 rounded-full px-6 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                                placeholder="Type your message...">
                            <button wire:click="sendMessage"
                                class="bg-blue-600 text-black rounded-full p-3 hover:bg-blue-700 transition-all shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center bg-gray-50">
                        <p class="text-gray-500 text-lg">Select a chat to start messaging</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- User Selector Modal -->
    @if ($showUserSelector)
        <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center">
            <div class="bg-white rounded-xl w-96 max-h-[80vh] overflow-hidden shadow-2xl">
                <div class="p-5 border-b">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">New Chat</h3>
                        <button wire:click="toggleUserSelector" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <input wire:model.live="searchQuery" type="text" class="w-full mt-4 px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                        placeholder="Search users...">
                </div>
                <div class="overflow-y-auto max-h-[60vh]">
                    @foreach ($companyUsers as $companyUser)
                        <button wire:click="startChat({{ $companyUser->user->id }})"
                            class="w-full text-left px-5 py-4 hover:bg-gray-50 transition-all flex items-center">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold shadow-md">
                                {{ substr($companyUser->user->name, 0, 1) }}
                            </div>
                            <span class="ml-4 text-gray-700 font-medium">{{ $companyUser->user->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
