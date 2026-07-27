import DataTable from "datatables.net-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.css";

document.addEventListener("DOMContentLoaded", () => {
    new DataTable("#memberTable", {
        pageLength: 10,
        responsive: true,
        ordering: true,
        searching: true,
    });

    const statusModal = document.getElementById("statusModal");
    const statusForm = document.getElementById("statusForm");
    const statusInput = document.getElementById("statusInput");
    const statusMessage = document.getElementById("statusModalMessage");
    const confirmButton = document.getElementById("statusConfirmButton");

    statusModal.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;

        const memberName = button.dataset.memberName;
        const status = button.dataset.status;
        const action = button.dataset.action;

        const activating = status === "1";

        statusForm.action = action;
        statusInput.value = status;

        statusMessage.textContent = `Are you sure you want to ${activating ? "activate" : "deactivate"} ${memberName}?`;

        confirmButton.textContent = activating ? "Activate" : "Deactivate";

        confirmButton.classList.remove("btn-success", "btn-danger");

        confirmButton.classList.add(activating ? "btn-success" : "btn-danger");
    });

    statusForm.addEventListener("submit", () => {
        confirmButton.disabled = true;

        confirmButton.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
                aria-hidden="true"
            ></span>
            Processing...
        `;
    });
});
