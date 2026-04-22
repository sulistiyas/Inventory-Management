import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
// alpine components
import datatable from './alpine/datatable';

// register alpine components
window.datatable = datatable;

// start alpine
Alpine.start();
