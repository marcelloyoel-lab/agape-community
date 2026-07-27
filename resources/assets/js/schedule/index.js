import DataTable from "datatables.net-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.css";

document.addEventListener("DOMContentLoaded", () => {
    new DataTable("#schedules-table", {
        pageLength: 10,
        responsive: true,
        ordering: true,
        searching: true,
    });
});
