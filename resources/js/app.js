import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// alpine components
import datatable from './alpine/components/datatable';
import modal from './alpine/components/modal';

// alpine pages
import productManager from './alpine/pages/product';
import stockManager from './alpine/pages/stockmovement';
import stockForm from './alpine/pages/stock_in_out';

// register alpine pages
window.productManager = productManager;
window.stockManager = stockManager;
window.stockForm = stockForm;

// register alpine components
window.datatable = datatable;
window.modal = modal;

// start alpine
Alpine.start();
