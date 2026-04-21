import axios from 'axios';
import 'preline'
import './preline-ui'
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.confirmDialog = (params) => window.dispatchEvent(new CustomEvent('open-modal', {
    detail: {
        name: 'confirm-dialog',
        ...params
    }
}))
