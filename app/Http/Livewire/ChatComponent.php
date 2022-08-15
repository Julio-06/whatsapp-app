<?php

namespace App\Http\Livewire;

use App\Models\Chat;
use App\Models\Contact;
use App\Notifications\NewMessage;
use App\Notifications\UserTyping;
use Livewire\Component;

use Illuminate\Support\Facades\Notification;

class ChatComponent extends Component
{
    public $search;

    public $contactChat, $chat, $chat_id;

    public $bodyMessage;

    public $users;

    public function mount()
    {
        $this->users = collect();
    }

    //OYENTES
    public function getListeners()
    {
        $user_id = auth()->user()->id;

        //EVENTO QUE ESTAMOS ESPERANDO ESCUCHAR
        return [
            "echo-notification:App.Models.User.{$user_id},notification" => 'render',
            "echo-presence:chat.1,here" => 'chatHere',
            "echo-presence:chat.1,joining" => 'chatJoining',
            "echo-presence:chat.1,leaving" => 'chatLeaving',
        ];
    }

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

    public function getMessagesProperty()
    {
        //SE COLOCO DE ESA MANERA PARA RECUPERAR LOS NUEVOS CHATS ENVIADOS POR CADA MENSAJE DE VUELVE A HACER LA CONSULTA
        return $this->chat ? $this->chat->messages()->get() : [];
    }

    public function getChatsProperty()
    {
        return auth()->user()->chats()->get()->sortByDesc('last_message_at');
    }

    public function getUsersNotificationsProperty()
    {
        return $this->chat ? $this->chat->users->where('id', '!=', auth()->id()) : collect();
    }

    public function getActiveProperty()
    {
        return $this->users->contains($this->users_notifications->first()->id);
    }

    //CICLO DE VIDA
    public function updatedBodyMessage($value)
    {
        if($value){
            //NOTIFICAR AL USUARIO QUE SU CONTACTO ESTA ESCRIBIENDO
            Notification::send($this->users_notifications, new UserTyping($this->chat->id));
        }
    }

    //MÉTODOS   
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
            $this->chat_id = $chat->id;
            $this->reset('contactChat', 'bodyMessage', 'search');

        }else{
            $this->contactChat = $contact;
            $this->reset('chat', 'bodyMessage', 'search');

        }

    }

    public function open_chat(Chat $chat)
    {
        $this->chat = $chat;
        $this->chat_id = $chat->id;
        $this->reset('contactChat', 'bodyMessage');

        $chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
            'is_read' => true
        ]);

        Notification::send($this->users_notifications, new NewMessage());

    }

    public function sendMessage()
    {
        $this->validate([
            'bodyMessage' => 'required|string'
        ]);

        if(!$this->chat){
            $this->chat = Chat::create();
            $this->chat_id = $this->chat->id;
            $this->chat->users()->attach([auth()->user()->id, $this->contactChat->contact_id]);
        }

        $this->chat->messages()->create([
            'body' => $this->bodyMessage,
            'user_id' => auth()->user()->id
        ]);

        //ENVIAMOS LA NOTIFICACIÓN AL USUARIO CON EL QUE ESCRIBIMOS
        Notification::send($this->users_notifications, new NewMessage());

        $this->reset('bodyMessage', 'contactChat');
    }

    public function chatHere($users)
    {
        $this->users = collect($users)->pluck('id');
    }

    public function chatJoining($user)
    {
        $this->users->push($user['id']);
    }

    public function chatLeaving($user)
    {
        $this->users = $this->users->filter(function($id) use($user) {
            return $id != $user['id'];
        });
    }

    public function render()
    {
        if($this->chat){
            $this->emit('scrollIntoView');
        }

        return view('livewire.chat-component')->layout('layouts.chat');
    }
}
