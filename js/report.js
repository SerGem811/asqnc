//this is the 1st way.
$(document).ready(function () {
    get_school_reports();
    function generate_bar_chart(answers, label, styles) {
        let percents = new Array();
        let percent_total = 0;
        let offset = 0;
        $.each(answers, function(index, obj){
            let p = parseFloat(obj['percent']);

            percent_total += p;
            if(p < 5 && p > 0) {
                offset += (5 - p);
                p = 5;
            }
            percents.push(p);
        });

        if(offset > 0) {
            let cnt = 0;
            for(let value of Object.values(percents)) {
                if(value > 5) {
                    cnt++;
                }
            }
            for (let i = 0 ; i < percents.length; i++) {
                if(percents[i] > 5) {
                    percents[i] -= (offset/cnt);
                }
            }
        }

        let html = '';
        html += '<div class="col-md-3"><p class="info"> ' + label + '</p></div>';
        html += '<div class="col-md-9">';

        $.each(answers, function(index, obj){
            if(percents[index - 1] > 0) {
                let show = percents[index - 1] / percent_total * 100;
                html += '<div style="width:' + show + '%;float:left;background-color:'+ styles[obj['content']] + '" class="bargraph">';
                html += '<div class="graph-no">' + obj['percent'] + '%</div></div>';
            }
        });
        html += '</div>';

        return html;
    }
    function get_school_reports() {
        
        let url = base_url + "/ajax.php?action=reports&s_id=" + $('#hide_school_id').val() + "&d_id=" + $('#hide_district_id').val();
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
                            let div_stack = 0;

                            let district_id = data['district_id'];
                            let school_id = data['school_id'];

                            $.each(data.result, function (prefix, obj) {
                                // category has changed
                                if (category !== obj['category']) {

                                    if(div_stack === 1){
                                        html += '</div>';
                                        div_stack = 0;
                                    }
                                    html += '<h4 class="q_category">' + obj['category'] + '</h4>';
                                    html += '<div class="q_category_details row">';
                                    div_stack = 1;
                                    category = obj['category'];
                                }
                                // show question
                                html += '<h5 class="q_title col-12">' + obj['prefix'] + '.' + obj['content'] + '</h5>';
                                // show answer header
                                html += '<div class="q_legend_info col-12"><div class="row">';
                                let styles = new Object();
                                $.each(obj['answer_header'], function(index, answer_header){
                                    html += '<div class="col-md-3 legend-title-col"><span class="legendBox" style="background-color: ' + answer_header['style'] + '">&nbsp;&nbsp;</span>';
                                    html += answer_header['content'];
                                    html += '</div>';
                                    styles[answer_header['content']] = answer_header['style'];
                                });
                                html += '</div></div>';

                                // show values
                                if(Object.keys(obj['answer']).length > 0) {
                                    html += '<div class="col-md-12 " >';
                                    html += '<div class="row row-h-bar-chart">';

                                    if(district_id == -1 && school_id == -1) {
                                        html += generate_bar_chart(obj['answer'], 'State', styles);
                                    } else if(district_id != -1 && school_id == -1) {
                                        html += generate_bar_chart(obj['answer_s'], 'State', styles);
                                        html += generate_bar_chart(obj['answer_d'], 'District', styles);
                                    } else {
                                        html += generate_bar_chart(obj['answer_s'], 'State', styles);
                                        html += generate_bar_chart(obj['answer_d'], 'District', styles);
                                        html += generate_bar_chart(obj['answer'], 'School', styles);
                                    }
                                }

                                // show sub questions
                                if(Object.keys(obj['subs']).length > 0) {
                                    $.each(obj['subs'], function(index, q_sub){
                                        html += '<div class="col-md-12 ">';
                                        html += '<div class="row row-h-bar-chart"><div class="col-md-12">';
                                        html += '<h5 class="q_sub_title">' + q_sub['content'] + '</h5>'
                                        html += '</div>';
                                        // draw bar chart
                                        if(district_id == -1 && school_id == -1) {
                                            html += generate_bar_chart(q_sub['answer'], 'State', styles);
                                        } else if(district_id != -1 && school_id == -1) {
                                            html += generate_bar_chart(q_sub['answer_s'], 'State', styles);
                                            html += generate_bar_chart(q_sub['answer_d'], 'District', styles);
                                        } else {
                                            html += generate_bar_chart(q_sub['answer_s'], 'State', styles);
                                            html += generate_bar_chart(q_sub['answer_d'], 'District', styles);
                                            html += generate_bar_chart(q_sub['answer'], 'School', styles);
                                        }

                                        html += '</div>';
                                        html += '</div>'
                                    });
                                }

                            });
                            if(div_stack === 1) {
                                html += '</div>';
                                div_stack = 0;
                            }
                            $('.reports-col').html(html);
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

