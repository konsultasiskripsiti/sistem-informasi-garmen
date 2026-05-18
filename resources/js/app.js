import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import DataTable from 'datatables.net-dt';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.DataTable = DataTable;

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }

    document.querySelectorAll('[data-datatable]').forEach((table) => {
        if (table.dataset.datatableInitialized === 'true') {
            return;
        }

        new DataTable(table, {
            paging: true,
            searching: true,
            info: true,
            pageLength: 10,
            lengthChange: false,
            order: [],
        });

        table.dataset.datatableInitialized = 'true';
    });

    document.querySelectorAll('input[type="date"]').forEach((input) => {
        if (input.dataset.datePickerInitialized === 'true') {
            return;
        }

        const openPicker = () => {
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            }
        };

        input.addEventListener('focus', openPicker);
        input.addEventListener('click', openPicker);
        input.dataset.datePickerInitialized = 'true';
    });
});
