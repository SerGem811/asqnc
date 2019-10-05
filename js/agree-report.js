//this is the 1st way.
$(document).ready(function () {
  get_school_reports();
  function get_percent_box(percent) {
    let html = '';
    percent = parseFloat(percent);
    let class_name = "col-1";

    if (percent < 29) {
      html += '<div class="' + class_name + ' px-0"><div class="percent-box range27">' + percent + '%</div></div>';
    } else if (percent < 49) {
      html += '<div class="' + class_name + ' px-0"><div class="percent-box range48">' + percent + '%</div></div>';
    } else if (percent < 69) {
      html += '<div class="' + class_name + ' px-0"><div class="percent-box range69">' + percent + '%</div></div>';
    } else {
      html += '<div class="' + class_name + ' px-0"><div class="percent-box range90">' + percent + '%</div></div>';
    }
    return html;
  }
  function get_school_reports() {
    let url = base_url + "/ajax.php?action=agree_reports&s_id=" + $('#hide_school_id').val() + "&d_id=" + $('#hide_district_id').val();
    let options = {
      type: 'post',
      url: url,
      dataType: 'json',
      cache: false,
      beforeSend: function () {

      },
      success: function (data) {
        if (data != null) {
          $('.loading-wrap').hide();
          if (data.success == true) {
            let html = '';
            if (data.result !== null) {
              let category = "";
              let district_id = data['district_id'];
              let school_id = data['school_id'];

              console.log(data.result);
              $.each(data.result, function (prefix, obj) {

                
                if (category !== obj['category']) {
                  if (prefix !== 0)
                    html += '</div>';
                  html += '<div style="margin-bottom: 50px;">';

                  html += '<div class="row" style="padding: 0 35px;">';
                  html += '<h4 class="q_category" style="width:50%">' + obj['category'] + '</h4>';

                  if (district_id != -1 && school_id != -1) {
                    html += '<h4 class="q_category" style="width:50%; text-align:right;">' + 'School | District | State' + '</h4>';
                  } else if (district_id != -1 && school_id == -1) {
                    html += '<h4 class="q_category" style="width:50%; text-align:right;">' + 'District | State' + '</h4>';
                  } else {
                    html += '<h4 class="q_category" style="width:50%; text-align:right;">' + 'State' + '</h4>';
                  }

                  html += '</div>';
                  category = obj['category'];
                }
                html += '<h5 class="q_title col-12">' + obj['prefix'] + '.' + obj['content'] + '</h5>';

                if (Object.keys(obj['answer']).length > 0) {
                  html += '<div class="agree-percent-row" style="padding-right: 0px !important;">';
                  html += '<div class="col-12" style="border-bottom: 1px solid #333;padding-right:0px !important;">';
                  html += '<div class="row mx-0">';

                  if (district_id == -1 && school_id == -1) {
                    // title
                    html += '<div class="col-11"><h5 class="q_sub_title">' + obj['content'] + '</h5></div>';
                    // state
                    html += get_percent_box(obj['answer']['3']['percent']);
                  } else if (district_id != -1 && school_id == -1) {
                    html += '<div class="col-10" style="border-bottom:1px solid #333;padding-right:0px !important;">';
                    // district
                    html += get_percent_box(obj['answer_d']['3']['percent']);
                    // state
                    html += get_percent_box(obj['answer_s']['3']['percent']);
                  } else {
                    // title
                    html += '<div class="col-9"><h5 class="q_sub_title">' + obj['content'] + '</h5></div>';
                    // school
                    html += get_percent_box(obj['answer']['3']['percent']);
                    // district
                    html += get_percent_box(obj['answer_d']['3']['percent']);
                    // state
                    html += get_percent_box(obj['answer_s']['3']['percent']);
                  }

                  html += '</div></div>';
                  html += '</div>';
                }

                if (Object.keys(obj['subs']).length > 0) {
                  html += '<div class="agree-percent-row" style="padding-right: 0px !important;">';
                  $.each(obj['subs'], function (index, q_sub) {
                    html += '<div class="col-12" style="border-bottom: 1px solid #333;padding-right:0px !important;">';
                    html += '<div class="row mx-0">';

                    if (district_id == -1 && school_id == -1) {
                      // title
                      html += '<div class="col-11"><h5 class="q_sub_title">' + q_sub['content'] + '</h5></div>';
                      // state
                      html += get_percent_box(q_sub['answer']['3']['percent']);
                    } else if (district_id != -1 && school_id == -1) {
                      // title
                      html += '<div class="col-10"><h5 class="q_sub_title">' + q_sub['content'] + '</h5></div>';
                      // district
                      html += get_percent_box(q_sub['answer_d']['3']['percent']);
                      // state
                      html += get_percent_box(q_sub['answer_s']['3']['percent']);
                    } else {
                      // title
                      html += '<div class="col-9"><h5 class="q_sub_title">' + q_sub['content'] + '</h5></div>';
                      // school
                      html += get_percent_box(q_sub['answer']['3']['percent']);
                      // district
                      html += get_percent_box(q_sub['answer_d']['3']['percent']);
                      // state
                      html += get_percent_box(q_sub['answer_s']['3']['percent']);
                    }
                    html += '</div></div>';
                  });
                  html += '</div>';
                }
              });

              html += '</div>';
              $('.reports-col').html(html);

            } else {

            }

          }
        }
      },
      error: function (request, status, error) {
        alert(status + ", " + error);
        $('.loading-wrap').hide();

      }
    }; // end ajax
    $.ajax(options);
  }

});
