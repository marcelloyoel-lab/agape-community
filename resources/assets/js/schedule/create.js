console.log("hi");
import $ from "jquery";
import select2 from "select2";
import "select2/dist/css/select2.css";

select2($);

document.addEventListener("DOMContentLoaded", () => {
    $(".member-select").each(function () {
        const $select = $(this);

        $select.select2({
            width: "100%",
            placeholder: $select.data("placeholder"),
            allowClear: !$select.prop("multiple"),
        });
    });
});
