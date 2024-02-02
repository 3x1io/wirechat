<?php

namespace Namu\WireChat\Livewire\Chat;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
//use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Namu\WireChat\Models\Attachment;

class ChatBox extends Component
{

    use WithFileUploads;

    public Conversation $conversation;

    public $receiver;
    public $body;

    public $loadedMessages;
    public $paginate_var = 20;


    public $photos = [];



    /**
     * livewire method
     ** This is avoid replacing temporary files on next selection
     * We override the function in WithFileUploads Trait
     */
    function _finishUpload($name, $tmpPath, $isMultiple)
    {
        $this->cleanupOldUploads();


        $files = collect($tmpPath)->map(function ($i) {
            return TemporaryUploadedFile::createFromLivewire($i);
        })->toArray();
        $this->dispatch('upload:finished', name: $name, tmpFilenames: collect($files)->map->getFilename()->toArray())->self();

        // If the property is an array, APPEND the upload to the array.
        $currentValue = $this->getPropertyValue($name);

        if (is_array($currentValue)) {
            $files = array_merge($currentValue, $files);
        } else {
            $files = $files[0];
        }

        app('livewire')->updateProperty($this, $name, $files);
    }


    function listenBroadcastedMessage($event)
    {

        // dd('reached');
        $this->dispatch('scroll-bottom');
        $newMessage = Message::find($event['message_id']);



        #push message
        $this->loadedMessages->push($newMessage);

        #mark as read
        $newMessage->read_at = now();
        $newMessage->save();
    }


    function sendMessage()
    {

        //dd( $this->body);

        if ($this->photos == null) {

            $this->validate(['body' => 'required|string']);
        }


        if ($this->photos != null) {


            $createdMessages = [];
            foreach ($this->photos as $key => $photo) {

                /**
                 * todo: Add url to table
                 */

                #save photo to disk 
                $path =  $photo->store('photos', 'public');

                #create attachment
                $attachment = Attachment::create([
                    'file_path' => $path,
                    'file_name' => basename($path),
                    'mime_type' => $photo->getMimeType(),
                ]);


                #create message
                $message = Message::create([
                    'conversation_id' => $this->conversation->id,
                    'attachment_id' => $attachment->id,
                    'sender_id' => auth()->id(),
                    'receiver_id' => $this->receiver->id,
                    // 'body'=>$this->body
                ]);

                #append message to createdMessages
                $createdMessages[] = $message;




                #update the conversation model - for sorting in chatlist
                $this->conversation->updated_at = now();
                $this->conversation->save();

                #dispatch event 'refresh ' to chatlist 
                $this->dispatch('refresh')->to(ChatList::class);
            }


            #push the message
            $this->loadedMessages = $this->loadedMessages->concat($createdMessages);

            #scroll to bottom
            $this->dispatch('scroll-bottom');
            //  dd($this->loadedMessages);



        }


        if ($this->body != null) {

            $createdMessage = Message::create([
                'conversation_id' => $this->conversation->id,
                'sender_id' => auth()->id(),
                'receiver_id' => $this->receiver->id,
                'body' => $this->body
            ]);



            $this->reset('body');

            #push the message
            $this->loadedMessages->push($createdMessage);


            #update the conversation model - for sorting in chatlist
            $this->conversation->updated_at = now();
            $this->conversation->save();

            #dispatch event 'refresh ' to chatlist 
            $this->dispatch('refresh')->to(ChatList::class);
        }
        $this->reset('photos');

        #scroll to bottom
        $this->dispatch('scroll-bottom');
    }


    function sendLike()
    {


        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'attachment_id' => null,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiver->id,
            'body' => '❤️'
        ]);


        #update the conversation model - for sorting in chatlist
        $this->conversation->updated_at = now();
        $this->conversation->save();

         #push the message
         $this->loadedMessages->push($message);

        #dispatch event 'refresh ' to chatlist 
        $this->dispatch('refresh')->to(ChatList::class);





        #scroll to bottom
        $this->dispatch('scroll-bottom');
    }



    #[On('loadMore')]
    function loadMore()
    {

        //dd('reached');

        #increment
        $this->paginate_var += 10;

        #call loadMessage
        $this->loadMessages();

        #dispatch event- update height
        $this->dispatch('update-height');
    }


    function loadMessages()
    {

        #get count
        $count = Message::where('conversation_id', $this->conversation->id)->count();

        #skip and query

        $this->loadedMessages = Message::where('conversation_id', $this->conversation->id)
            ->skip($count - $this->paginate_var)
            ->take($this->paginate_var)
            ->get();

        return $this->loadedMessages;
    }

    function mount()
    {

        $this->receiver = $this->conversation->getReceiver();

        $this->loadMessages();
    }

    public function render()
    {
        return view('wirechat::livewire.chat.chat-box');
    }
}