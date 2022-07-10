<?php

namespace App\Http\Livewire;

use App\Models\Contact;
use Livewire\Component;

class ChatComponent extends Component
{
    public $search;

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

    public function render()
    {
        return view('livewire.chat-component')->layout('layouts.chat');
    }
}
