<?php

use App\Models\ChatRoomUser;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    return ChatRoomUser::where('chat_room_id', $roomId)
        ->where('user_id', $user->id)
        ->exists();
});
