import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// alpine components
import datatable from './alpine/components/datatable';
import modal from './alpine/components/modal';

// alpine pages
import productManager from './alpine/pages/product';

// register alpine pages
window.productManager = productManager;

// register alpine components
window.datatable = datatable;
window.modal = modal;

// start alpine
Alpine.start();
