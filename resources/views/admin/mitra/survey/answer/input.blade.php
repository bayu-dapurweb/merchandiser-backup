
<div class="row" style="margin-bottom:4px;">
    <div class="col-sm-3">
        <label>{{ $answer['answer'] }}<label>
    </div>
    <div class="col-sm-6">
        <input type="text" name="answer[{{$answer['id']}}]" class="form-control" 
            id="answer-{{$answer['id']}}" 
            data-ref_mitras_id="{{$ref_mitras_id}}"
            data-cms_users_id="{{$cms_users_id}}"
            data-ref_survey_questions_id="{{$ref_survey_questions_id}}"
            data-ref_survey_answers_id="{{$ref_survey_answers_id}}"
            data-answer_type="{{$answer_type}}"
            value="{{$current_value}}"
        >
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