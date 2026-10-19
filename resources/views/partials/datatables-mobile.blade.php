{{-- Include after jquery.dataTables.js and before DataTable() init --}}
<script>
    if (window.jQuery && jQuery.fn.dataTable) {
        jQuery.extend(true, jQuery.fn.dataTable.defaults, {
            scrollX: true,
            autoWidth: false,
        });
    }
</script>
