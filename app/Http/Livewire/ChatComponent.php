<?php

namespace App\Http\Livewire;

use App\Models\Chat;
use App\Models\Contact;
use Livewire\Component;

class ChatComponent extends Component
{
    public $search;

    public $contactChat, $chat;

    public $bodyMessage;

    //PROPIEDAD COMPUTADAS
    public function getContactsProperty()
    {
        return Contact::where('user_id', auth()->user()->id)
            ->when($this->search, function($q){
                $q->where(function($q){
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function($q){
                            $q->where('email', 'like', '%' . $this->search . '%');
                        });
                });
                
            })
            ->get() ?? [];
    }

    public function open_chat_contact(Contact $contact)
    {
        $chat = auth()->user()
                ->chats()
                ->whereHas('users', function($q) use ($contact) {
                    $q->where('user_id', $contact->contact_id);
                })
                //LIMITAR QUE EXISTA EL CHAT PARA DOS USUARIOS PARA QUE NO TRAIGA CHATS GRUPALES
                ->has('users', 2)
                ->first();

        if($chat){
            $this->chat = $chat;
        }else{
            $this->contactChat = $contact;
        }
    }

    public function sendMessage()
    {
        $this->validate([
            'bodyMessage' => 'required|string'
        ]);

        if(!$this->chat){
            $this->chat = Chat::create();

            $this->chat->users()->attach([auth()->user()->id, $this->contactChat->contact_id]);
        }

        $this->chat->messages()->create([
            'body' => $this->bodyMessage,
            'user_id' => auth()->user()->id
        ]);

        $this->reset('bodyMessage', 'contactChat');
    }

    public function render()
    {
        return view('livewire.chat-component')->layout('layouts.chat');
    }
}
