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
    public $userColors = [];

    public function mount()
    {
        if (!Session::has('user_id')) {
            $this->userId = uniqid('user_', true);
            Session::put('user_id', $this->userId);
        } else {
            $this->userId = Session::get('user_id');
        }

        $this->toggleSwitch = Cache::get('toggleSwitch', false);
        $this->userColors[$this->userId] = $this->generateRandomColor();
    }

    public function generateRandomColor()
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xffffff)), 6, '0', STR_PAD_LEFT);
    }

    public function flipSwitch()
    {
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
        if (!isset($this->userColors[$payload['userId']])) {
            $this->userColors[$payload['userId']] = $this->generateRandomColor();
        }
    }

    public function moveMouse($position)
    {
        $payload = [
            'userId' => $this->userId,
            'position' => $position,
            'color' => $this->userColors[$this->userId],
        ];

        broadcast(new MouseMoved($payload))->toOthers();
        $this->mousePositions[$this->userId] = $position;
    }

    public function setInactive()
    {
        unset($this->mousePositions[$this->userId]);
        broadcast(new MouseMoved(['userId' => $this->userId, 'position' => null]))->toOthers();
    }

    public function getMousePositionsProperty()
    {
        return collect($this->mousePositions)
            ->except($this->userId)
            ->toArray();
    }
}; ?>
<div x-data="{ localToggle: @entangle('toggleSwitch') }">
    <div class="flex items-center justify-center min-h-screen">
        <label for="toggleSwitch" class="flex items-center cursor-pointer">
            <div class="relative">
                <input type="checkbox" id="toggleSwitch" class="sr-only" x-model="localToggle"
                    x-on:change="$wire.flipSwitch()">
                <div class="block h-8 bg-gray-600 rounded-full w-14"></div>
                <div class="absolute left-1 top-1 w-6 h-6 rounded-full transition-transform duration-200"
                    x-bind:class="localToggle ? 'translate-x-full bg-green-400' : 'bg-white'">
                </div>
            </div>
        </label>
    </div>


    @foreach ($this->mousePositions as $userId => $position)
        @if ($userId !== $this->userId && $position)
            <div class="cursor-dot"
                style="left: calc(50% + {{ $position['x'] * 50 }}%);
                   top: calc(50% + {{ $position['y'] * 50 }}%);
                   background-color: {{ $this->userColors[$userId] ?? '#000000' }};">
            </div>
        @endif
    @endforeach


    <div class="fixed bottom-0 left-0 p-4 text-white bg-black bg-opacity-50">
        @foreach ($this->mousePositions as $userId => $position)
            @if ($position)
                <div>{{ $userId }}: [{{ $position['x'] }}, {{ $position['y'] }}]</div>
            @endif
        @endforeach
    </div>
</div>
