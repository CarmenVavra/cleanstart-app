import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { checkin, dashboard } from './dashboard';

Alpine.data('dashboard', dashboard);
Alpine.data('checkin', checkin);

Livewire.start();
