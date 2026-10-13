import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { checkin, dashboard } from './dashboard';
import { installApp, registerServiceWorker } from './install';

Alpine.data('dashboard', dashboard);
Alpine.data('checkin', checkin);
Alpine.data('installApp', installApp);

registerServiceWorker();

Livewire.start();
