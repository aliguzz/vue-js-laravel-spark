@extends('admin.layouts.app')

@section('content')

@include('admin.settings.subheader')

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!-- Validation -->
<script src="{{ asset('js/plugins/validation/jquery.validate.min.js')}}"></script>

<style>
    .dropdown-menu {
        min-width: auto;
    }
    #feedback { font-size: 1.4em; }
    #selectable .ui-selecting { background: none; }
    #selectable .ui-selected { background: none; color: #7ab428; }
    #selectable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #selectable tr { cursor:pointer; margin: 3px; padding: 0.4em; font-size: 1.4em; height: 18px; }
    table tr td{ color: rgb(207, 206, 206);}
   
</style>

<div class="container-fluid">
    <div id="loading" style="display: none;"></div>
    <section class="inner-full-width-panel pr-30">
            <div class="row">
            <div id="menu1" class="right-content-space fix-width">
                
                <div class="page-header">
                    <h1 class="Duplicate">App Settings</h1>
                </div>
                <div class="box-content">
                {!! Form::open(['url' => '/admin/settings/update', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data', 'files' => true]) !!}
                <div class="form_wrap border" style="max-width:100%;">
                    <div class="form-group {{ $errors->has('formation') ? 'has-error' : ''}}">
                        {!! Form::label('formation', 'Formation', ['class' => 'col-sm-12 control-label  padding0']) !!}
                        <code>e.g. 4-4-2, 3-4-3</code>
                        <div class="col-sm-12 padding0">
                            {!! Form::text('formation', (old('formation') ?:settingValue('formation')), ['class' => 'form-control']) !!}
                            {!! $errors->first('formation', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('banned_wordslist') ? 'has-error' : ''}}">
                        {!! Form::label('banned_wordslist', 'Banned Word List', ['class' => 'col-sm-12 control-label  padding0']) !!}
                        <div class="col-sm-12   padding0">
                        {!! Form::textarea('banned_wordslist', (old('banned_wordslist') ?:settingValue('banned_wordslist')), ['class' => 'form-control']) !!}
                            {!! $errors->first('banned_wordslist', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <!-- <div class="form-group {{ $errors->has('wildcard_used') ? 'has-error' : ''}}">
                        {!! Form::label('wildcard_used', 'Wildcard Used', ['class' => 'col-sm-12 control-label  padding0']) !!}
                        <div class="col-sm-12   padding0">
                            {!! Form::select('wildcard_used', ['' => 'Wildcard Used', '1' => 'Yes', '0' => 'No'], (old('wildcard_used') ?:settingValue('wildcard_used')), ['class' => 'form-control']) !!}
                            {!! $errors->first('wildcard_used', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div> -->

                    <!-- <div class="form-group {{ $errors->has('bench_boost_used') ? 'has-error' : ''}}">
                        {!! Form::label('bench_boost_used', 'Bench Boost Used', ['class' => 'col-sm-12 control-label  padding0']) !!}
                        <div class="col-sm-12   padding0">
                            {!! Form::select('bench_boost_used', ['' => 'Bench Boost Used', '1' => 'Yes', '0' => 'No'], (old('bench_boost_used') ?:settingValue('bench_boost_used')), ['class' => 'form-control']) !!}
                            {!! $errors->first('bench_boost_used', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div> -->

                    <!-- <div class="form-group {{ $errors->has('triple_captain_used') ? 'has-error' : ''}}">
                        {!! Form::label('triple_captain_used', 'Triple Captain Used', ['class' => 'col-sm-12 control-label  padding0']) !!}
                        <div class="col-sm-12   padding0">
                            {!! Form::select('triple_captain_used', ['' => 'Triple Captain Used', '1' => 'Yes', '0' => 'No'], (old('triple_captain_used') ?:settingValue('triple_captain_used')), ['class' => 'form-control']) !!}
                            {!! $errors->first('triple_captain_used', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div> -->
                    
                    <div class="form-group {{ $errors->has('free_transfer_used') ? 'has-error' : ''}}">
                        {!! Form::label('free_transfer_used', 'Free Transfer Used', ['class' => 'col-sm-12 control-label  padding0']) !!}
                        <div class="col-sm-12   padding0">
                            {!! Form::select('free_transfer_used', ['' => 'Free Transfer Used', '1' => 'Yes', '0' => 'No'], (old('free_transfer_used') ?:settingValue('free_transfer_used')), ['class' => 'form-control']) !!}
                            {!! $errors->first('free_transfer_used', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('budget') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Budget <code>(£M)</code></label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('budget', (old('budget') ?:settingValue('budget')), ['class' => 'form-control']) !!}
                            {!! $errors->first('budget', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <h2>Scores Settings</h2>
                    <div class="form-group {{ $errors->has('scores_gk_plus_clean_sheet') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Points GoalKeeper & Clean Sheet</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_gk_plus_clean_sheet', (old('scores_gk_plus_clean_sheet') ?:settingValue('scores_gk_plus_clean_sheet')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_gk_plus_clean_sheet', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_mid_plus_clean_sheet') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Points Midfielder & Clean Sheet</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_mid_plus_clean_sheet', (old('scores_mid_plus_clean_sheet') ?:settingValue('scores_mid_plus_clean_sheet')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_mid_plus_clean_sheet', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>
                   
                    <div class="form-group {{ $errors->has('scores_def_plus_clean_sheet') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Points Defender & Clean Sheet</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_def_plus_clean_sheet', (old('scores_def_plus_clean_sheet') ?:settingValue('scores_def_plus_clean_sheet')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_def_plus_clean_sheet', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_gk_plus_number_of_goals') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Points GoalKeeper & Number of Goals</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_gk_plus_number_of_goals', (old('scores_gk_plus_number_of_goals') ?:settingValue('scores_gk_plus_number_of_goals')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_gk_plus_number_of_goals', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_def_plus_number_of_goals') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Points Defender & Number of Goals</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_def_plus_number_of_goals', (old('scores_def_plus_number_of_goals') ?:settingValue('scores_def_plus_number_of_goals')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_def_plus_number_of_goals', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_mid_plus_number_of_goals') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Midfielder & Number of Goals</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_mid_plus_number_of_goals', (old('scores_mid_plus_number_of_goals') ?:settingValue('scores_mid_plus_number_of_goals')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_mid_plus_number_of_goals', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_for_plus_number_of_goals') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Forward & Number of Goals</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_for_plus_number_of_goals', (old('scores_for_plus_number_of_goals') ?:settingValue('scores_for_plus_number_of_goals')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_for_plus_number_of_goals', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_hattrick') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Hattrick</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_hattrick', (old('scores_hattrick') ?:settingValue('scores_hattrick')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_hattrick', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_number_of_assists') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Number of Assists</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_number_of_assists', (old('scores_number_of_assists') ?:settingValue('scores_number_of_assists')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_number_of_assists', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_gk_plus_penalty_save') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores GoalKeeper & Penalty Save</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_gk_plus_penalty_save', (old('scores_gk_plus_penalty_save') ?:settingValue('scores_gk_plus_penalty_save')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_gk_plus_penalty_save', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_penalty_miss') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Penalty Miss</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_penalty_miss', (old('scores_penalty_miss') ?:settingValue('scores_penalty_miss')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_penalty_miss', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_number_of_yellow_cards') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Number of Yellow Cards</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_number_of_yellow_cards', (old('scores_number_of_yellow_cards') ?:settingValue('scores_number_of_yellow_cards')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_number_of_yellow_cards', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_number_of_red_cards') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Number of Red Cards</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_number_of_red_cards', (old('scores_number_of_red_cards') ?:settingValue('scores_number_of_red_cards')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_number_of_red_cards', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_def_gk_plus_number_of_goals_conceded') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores GoalKeeper/Defender & Number of Goals Conceded</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_def_gk_plus_number_of_goals_conceded', (old('scores_def_gk_plus_number_of_goals_conceded') ?:settingValue('scores_def_gk_plus_number_of_goals_conceded')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_def_gk_plus_number_of_goals_conceded', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_mid_plus_number_of_goals_conceded') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Midfielder & Number of Goals Conceded</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_mid_plus_number_of_goals_conceded', (old('scores_mid_plus_number_of_goals_conceded') ?:settingValue('scores_mid_plus_number_of_goals_conceded')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_mid_plus_number_of_goals_conceded', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('match_start') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Match Start</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('match_start', (old('match_start') ?:settingValue('match_start')), ['class' => 'form-control']) !!}
                            {!! $errors->first('match_start', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_played_for_60_mins') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Played For 60 Minutes</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_played_for_60_mins', (old('scores_played_for_60_mins') ?:settingValue('scores_played_for_60_mins')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_played_for_60_mins', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <h2>Bonus Settings</h2>
                    <div class="form-group {{ $errors->has('scores_best_player') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores First Best Player</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_best_player', (old('scores_best_player') ?:settingValue('scores_best_player')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_best_player', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_second_best_player') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Second Best Player</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_second_best_player', (old('scores_second_best_player') ?:settingValue('scores_second_best_player')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_second_best_player', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('scores_third_best_player') ? 'has-error' : ''}}">
                        <label class='col-sm-12 control-label padding0'>Scores Third Best Player</label>
                        <div class="col-sm-12  padding0">
                            {!! Form::number('scores_third_best_player', (old('scores_third_best_player') ?:settingValue('scores_third_best_player')), ['class' => 'form-control']) !!}
                            {!! $errors->first('scores_third_best_player', '<p class="help-block text-danger">:message</p>') !!}
                        </div>
                    </div>

                    <!-- end scores settings -->

                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-12 text-right padding0">
                            <button type="submit" class="btn btn-preview">Update Settings</button>
                        </div>
                    </div>
                </div>    
                {!! Form::close() !!}
            </div>
                
                
               
            </div>
            </div>
    </section>
</div>


<script type="text/javascript">
$(document).ready(function () {
	$('.remove_btn').click(function(){
		  
		  $("#image-upload").val('');
		
		});

});
</script>
<script>
        function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#image_upload_preview').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#image-upload").change(function () {
        readURL(this);
    });
</script>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function () {

    $.uploadPreview({
        input_field: "#image-upload", // Default: .image-upload
        preview_box: "#image-preview", // Default: .image-preview
        label_field: "#image-label", // Default: .image-label
        label_default: "Choose Logo", // Default: Choose File
        label_selected: "Change Logo", // Default: Change File
        no_label: false                 // Default: false
    });
	
	

});
</script>
@endsection
