<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Events\SwitchFlipped;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public $toggleSwitch = false;

    public function mount()
    {
        // Retrieve the toggle switch state from the cache
        $this->toggleSwitch = Cache::get('toggleSwitch', false);
    }

    public function flipSwitch()
    {
        $this->toggleSwitch = !$this->toggleSwitch;

        // Update the switch state in the cache
        Cache::forever('toggleSwitch', $this->toggleSwitch);

        // Broadcast the new state
        broadcast(new SwitchFlipped($this->toggleSwitch))->toOthers();
    }

    #[On('echo:switch,SwitchFlipped')]
    public function notifySwitchFlipped($payload)
    {
        $this->toggleSwitch = $payload['toggleSwitch'];
        // Optionally update the cache to keep it consistent
        Cache::forever('toggleSwitch', $this->toggleSwitch);
    }
}; ?>



<div class="flex items-center justify-center min-h-screen">
    <label for="toggleSwitch" class="flex items-center cursor-pointer">
        <!-- toggle -->
        <div class="relative">
            <!-- input -->
            <input type="checkbox" id="toggleSwitch" class="sr-only" wire:click="flipSwitch"
                {{ $toggleSwitch ? 'checked' : '' }}>
            <!-- line -->
            <div class="block h-8 bg-gray-600 rounded-full w-14"></div>
            <!-- dot -->
            <div
                class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition {{ $toggleSwitch ? 'translate-x-full bg-green-400' : '' }}">
            </div>
        </div>
        <!-- label -->
        <div class="ml-3 font-medium text-gray-700">
            {{ $toggleSwitch ? 'On' : 'Off' }}
        </div>
    </label>
</div>
