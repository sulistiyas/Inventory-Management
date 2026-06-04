import './bootstrap';
import Alpine from 'alpinejs';
import '../css/app.css';

window.Alpine = Alpine;

// alpine components
import datatable from './alpine/components/datatable';
import modal from './alpine/components/modal';

// alpine pages
import productManager from './alpine/pages/product';
import stockManager from './alpine/pages/stockmovement';
import stockForm from './alpine/pages/stock_in_out';
import posKasir from './alpine/pages/pos_kasir';
import serviceOrder from './alpine/pages/service_order';
import customerManager from './alpine/pages/customer';

// register alpine pages
window.productManager = productManager;
window.stockManager = stockManager;
window.stockForm = stockForm;
window.posKasir        = posKasir;
window.serviceOrder    = serviceOrder;
window.customerManager = customerManager;

// register alpine components
window.datatable = datatable;
window.modal = modal;

// start alpine
Alpine.start();
