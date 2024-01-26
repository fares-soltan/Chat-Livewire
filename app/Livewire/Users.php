<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\User;
use Livewire\Component;

class Users extends Component
{

    public function message($userId)
    {
        $authUserId = auth()->user()->id;

        # Check conversation existing
        $existingConversation =Conversation::where(function ($query) use ($authUserId,$userId){
            $query->where('sender_id',$userId)
                ->where('receiver_id',$authUserId);
        })->orWhere(function ($query) use ($authUserId,$userId){
            $query->where('sender_id',$authUserId)
                ->where('receiver_id',$userId);
        })->first();


        if ($existingConversation){
            return redirect()->route('chat',['query'=>$existingConversation->id]);
        }

        # Create Conversation

        $createdConversation = Conversation::create([
            'sender_id'=>$authUserId,
            'receiver_id'=>$userId
        ]);

        return redirect()->route('chat',['query'=>$createdConversation->id]);


    }

    public function render()
    {
        $users = User::where('id','!=',auth()->user()->id)->get();

        return view('livewire.users',compact('users'));
    }
}
