import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
// alpine components
import datatable from './alpine/datatable';
import modal from './alpine/modal';

// register alpine components
window.datatable = datatable;
window.modal = modal;

// start alpine
Alpine.start();
