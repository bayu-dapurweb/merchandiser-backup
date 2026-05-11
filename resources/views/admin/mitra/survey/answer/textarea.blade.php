
<div class="row" style="margin-bottom:4px;">
    <div class="col-sm-12">
        <span>{!! nl2br($answer['answer']) !!}<span>
    </div>
    <div class="col-sm-8">
        <textarea class="form-control" name="answer[{{$answer['id']}}]" rows="6"
            id="answer-{{$answer['id']}}" 
            data-ref_mitras_id="{{$ref_mitras_id}}"
            data-cms_users_id="{{$cms_users_id}}"
            data-ref_survey_questions_id="{{$ref_survey_questions_id}}"
            data-ref_survey_answers_id="{{$ref_survey_answers_id}}"
            data-answer_type="{{$answer_type}}"
        >{{$current_value}}</textarea>
    </div>
    <script>
        $("#answer-{{$answer['id']}}").change(function(){
            var value = $(this).val()

            param = {
                ref_mitras_id : $(this).attr('data-ref_mitras_id'),
                cms_users_id : $(this).attr('data-cms_users_id'),
                ref_survey_questions_id : $(this).attr('data-ref_survey_questions_id'),
                ref_survey_answers_id : $(this).attr('data-ref_survey_answers_id'),
                answer_type : $(this).attr('data-answer_type'),
                answer_value : value
            }

            sendanswer(param)
        });
    </script>
</div>