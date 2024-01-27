<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use App\Notifications\MessageSent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatBox extends Component
{
    public $selectedConversation;
    public $body;
    public $loadedMessages;
    public $paginate_var = 10;

    protected $listeners = ['loadMore'];

    public function getListeners()
    {
        $authId = auth()->user()->id;

        return [
            'loadMore',
            "echo-private:users.{$authId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated"=>'broadcastedNotification'
        ];
    }

    public function broadcastedNotification($event)
    {
        if ($event['type'] == MessageSent::class){

            if ($event['conversation_id'] == $this->selectedConversation->id){
                $this->dispatch('scroll-bottom');
                $newMessage = Message::find($event['message_id']);

                $this->loadedMessages->push($newMessage);
            }
        }
    }

    public function loadMore() : void
    {
        $this->paginate_var += 10;

        $this->loadMessages();

        $this->dispatch('update-chat-height');
    }

    public function loadMessages()
    {
       $count = $this->loadedMessages = Message::where('conversation_id',$this->selectedConversation->id)->count();

        $this->loadedMessages = Message::where('conversation_id',$this->selectedConversation->id)
            ->skip($count - $this->paginate_var)
            ->take($this->paginate_var)
            ->get();

        return $this->loadedMessages;
    }

    public function mount()
    {
        $this->loadMessages();
    }

    public function sendMessage()
    {
        $this->validate(['body' => 'required|string']);

        $sendMessage = Message::create([
            'conversation_id'=>$this->selectedConversation->id,
            'sender_id'=>auth()->id(),
            'receiver_id'=>$this->selectedConversation->getReceiver()->id,
            'body'=>$this->body,
        ]);

        $this->reset('body');

        #scroll to bottom
        $this->dispatch('scroll-bottom');


        ## Push message

        $this->loadedMessages->push($sendMessage);

        #update conversation model
        $this->selectedConversation->updated_at = now();
        $this->selectedConversation->save();

        #refresh chatlist
        $this->dispatch('refresh','chat.chat-list');

        # BroadCast

        $this->selectedConversation->getReceiver()
            ->notify(new MessageSent(
                Auth()->user(),
                $sendMessage,
                $this->selectedConversation,
                $this->selectedConversation->getReceiver()->id
            ));

    }

    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
