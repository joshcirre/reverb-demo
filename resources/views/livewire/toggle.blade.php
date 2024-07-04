<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Events\SwitchFlipped;
use App\Events\MouseMoved;
use App\Events\UserInactive;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

new class extends Component {
    public $toggleSwitch = false;
    public $mousePositions = [];
    public $userId;

    public function mount()
    {
        if (!Session::has('user_id')) {
            $this->userId = uniqid('user_', true);
            Session::put('user_id', $this->userId);
        } else {
            $this->userId = Session::get('user_id');
        }

        $this->toggleSwitch = Cache::get('toggleSwitch', false);
    }

    public function flipSwitch()
    {
        $this->toggleSwitch = !$this->toggleSwitch;
        Cache::forever('toggleSwitch', $this->toggleSwitch);
        broadcast(new SwitchFlipped($this->toggleSwitch))->toOthers();
    }

    #[On('echo:switch,SwitchFlipped')]
    public function notifySwitchFlipped($payload)
    {
        $this->toggleSwitch = $payload['toggleSwitch'];
        Cache::forever('toggleSwitch', $this->toggleSwitch);
    }

    #[On('echo:mouse-movement,MouseMoved')]
    public function notifyMouseMoved($payload)
    {
        $this->mousePositions[$payload['userId']] = $payload['position'];
    }

    public function moveMouse($position)
    {
        $payload = [
            'userId' => $this->userId,
            'position' => $position,
        ];

        broadcast(new MouseMoved($payload))->toOthers();
        $this->mousePositions[$this->userId] = [
            'position' => $position,
            'timestamp' => time(),
        ];
    }

    public function setInactive()
    {
        // Remove this user's position when they become inactive
        unset($this->mousePositions[$this->userId]);

        // Broadcast that this user is now inactive
        broadcast(new UserInactive($this->userId))->toOthers();
    }

    #[On('echo:mouse-movement,UserInactive')]
    public function removeInactiveUser($userId)
    {
        unset($this->mousePositions[$userId]);
    }
}; ?>

<div>
    <div class="flex items-center justify-center min-h-screen">
        <label for="toggleSwitch" class="flex items-center cursor-pointer">
            <div class="relative">
                <input type="checkbox" id="toggleSwitch" class="sr-only" wire:click="flipSwitch"
                    {{ $toggleSwitch ? 'checked' : '' }}>
                <div class="block h-8 bg-gray-600 rounded-full w-14"></div>
                <div
                    class="absolute left-1 top-1 w-6 h-6 rounded-full transition-transform duration-200 {{ $toggleSwitch ? 'translate-x-full bg-green-400' : 'bg-white' }}">
                </div>
            </div>
        </label>
    </div>

    <div class="fixed bottom-0 left-0 p-4 text-white bg-black bg-opacity-50">
        @foreach ($mousePositions as $userId => $position)
            @if ($position)
                <div>{{ $userId }}: [{{ $position['x'] }}, {{ $position['y'] }}]</div>
            @endif
        @endforeach
    </div>
</div>
