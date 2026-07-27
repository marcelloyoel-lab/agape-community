import DataTable from "datatables.net-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.css";

document.addEventListener("DOMContentLoaded", () => {
    new DataTable("#ministryTable", {
        pageLength: 10,
        responsive: true,
        ordering: true,
        searching: true,
    });

    document.querySelectorAll(".ministry-status-form").forEach((form) => {
        form.addEventListener("submit", () => {
            const button = form.querySelector('button[type="submit"]');

            button.disabled = true;
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                Updating...
            `;
        });
    });

    document.querySelectorAll(".ministry-delete-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
            const ministryName = form.dataset.ministryName;

            const confirmed = window.confirm(
                `Are you sure you want to delete "${ministryName}"?`,
            );

            if (!confirmed) {
                event.preventDefault();
                return;
            }

            const button = form.querySelector('button[type="submit"]');

            button.disabled = true;
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm"></span>
            `;
        });
    });
});
