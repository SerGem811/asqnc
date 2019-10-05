//this is the 1st way.
$(document).ready(function () {

    set_total();

    var url = base_url + '/ajax.php?action=get_school_list';
    $('#tbl-school-list').DataTable({
        "destroy": true, //use for reinitialize datatable	
        "sAjaxSource": url,
        searching: true,
        paging: true,
        info: true,
        dom: "lBfrtip",
        "lengthMenu": [
            [50, 100, 150, 300, 600, 900, 1200, 1500, -1],
            [50, 100, 150, 300, "All"] // change per page values here
        ],
        "pageLength": 50,

        "aoColumns": [
            {"mData": "district"},
            {"mData": "school"},
            {"mData": "reports", sClass: "reports"},
            {"mData": "total_invitees"},
            {"mData": "respondents"},
            {"mData": "respondents_pert"}
        ],
        responsive: true,
        "order": [
            [0, 'asc'],
            [1, 'asc']
        ],
    });

    // function btnshowloading(btn) {
    //     $(btn).prop("disabled", true);
    //     var html = '<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>  Loading...';
    //     $(btn).html(html);
    // }
    //
    // function btnhideloading(btn) {
    //     var btn_text = $(btn).attr('data-text');
    //     $(btn).prop("disabled", false);
    //     $(btn).html(btn_text);
    // }

    function set_total() {
        var url = base_url + "/ajax.php?action=get_total_rate";

        var options = {
            type: 'post',
            url: url,
            dataType: 'json',
            cache: false,
            beforeSend: function () {

            },
            success: function (data) {
                if (data != null) {
                    if(data.success == true) {
                        if(data.result != null) {
                            const invitees = formatNumber(data['result']['invitees'].toString());
                            const respondents = formatNumber(data['result']['respondents'].toString());
                            const rate = Math.ceil(data['result']['respondents'] / data['result']['invitees'] * 100);

                            $("#total_invitees").html(invitees);
                            $("#total_respondents").html(respondents);
                            $("#total_rate").html(rate + ' %');
                        }
                    }
                }
            },
            error: function (request, status, error) {
                alert(status + ", " + error);
                //$('.loading-wrap').hide();

            }
        }; // end ajax
        $.ajax(options);
    }

    function formatNumber(num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
    }
});
