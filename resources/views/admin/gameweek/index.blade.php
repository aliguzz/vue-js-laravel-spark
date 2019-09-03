@extends('admin.layouts.app')
@section('content')

<style>
    .dt-buttons {
        margin-top: 23px !important;
        margin-bottom: 14px !important;
    }

    .right-panel {
        background-color: #fff;
        padding: 20px 40px;
        border-radius: 7px;
        border-top: 14px solid #7ab428;
    }

    .table-responsive {
        overflow-x: scroll;
    }

    .mt15 {
        margin-top: 15px !important;
    }

    .active {
        background-color: #450086;
    }

</style>
<?php global $ids;?>
<section class="main_wrapper">
    <div class="left-panel-control" id="left-panel-open">
        <div id="site-menu">
            <div class="left-panel-heading float-left w-100 left-panel-inner-space mb-10" id="giveId">
                <a href="javascript:;" data-target="{{url('admin/gameweek-deadline')}}" style="margin-bottom:15px;"
                    class="btn btn-newsite gameweek_button">Game Deadline</a>
                @forelse($clubs as $clubb)
                <a href="javascript:;" id="{{$clubb->id}}" data-target="{{url('admin/gameweek/'.$clubb->id)}}"
                    class="btn btn-newsite gameweek_button {{!empty($club) && $club->id == $clubb->id ? 'active' : ''}}">{{$clubb->name}}</a>
                @empty
                <p>No clubs</p>
                @endforelse
            </div>

        </div>

    </div>

    <div class="right-panel">
        <h2>Game Week</h2>
        <div class="row">
            <div class='col-sm-4'>
                Deadline : <span>{{($deadline && $deadline->setting_value)?$deadline->setting_value:''}}</span>
            </div>

            <div class='col-sm-6'>
                Gameweek : <span>Current Game week is {{($weekNum)?  $weekNum.' ':' 0'}}</span>
            </div>

        </div>
        <br>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                @if(!empty($club))
                {!! Form::open(['url' => '/admin/gameweek', 'class' => 'form-horizontal', 'enctype' =>
                'multipart/form-data', 'files' => true]) !!}

                <lable>Week number</lable>
                <select class="selectpicker" data-live-search="true" name="form[week_date]" id="week_date">
                    {{-- <option>Select week</option> --}}
                    @if(!empty($weekDropdown))
                    @foreach($weekDropdown AS $val)
                    @if($val!=$current_week)
                    <option value="{{$val}}" {{($week==$val)?'selected':''}}>{{$val}} </option>
                    @endif
                    @endforeach

                    @endif
                    <option value="{{$current_week}}" {{($week==$current_week)?'selected':''}}>Current Week</option>
                </select><span class="fa fa-spinner fa-spin" style="display:none;margin-left: 5px;"></span>
                <br>
                <?php echo Form::hidden('form[clubID]', '', ['id' => 'clubID']) ?>
                <table style="display: none;" id="gameweek_table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Player Name</th>
                            <th>Position</th>
                            <th>Count In</th>
                            <th>Count Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($players as $player)
                        @php
                        $gameweek = getPlayerGameWeekData($player->id, $week);
                        $countIn = clubPlayersCountIn($player->id, $week);
                        $countOut = clubPlayersCountOut($player->id, $week);

                        $positionArray = [
                        'FOR'=> 'A',
                        'MID' => 'M',
                        'DEF' => 'D',
                        'GK'=> 'G'];

                        $position = $player->position;
                        if(array_key_exists($position, $positionArray)){
                        $position = $positionArray[$position];
                        }

                        @endphp
                        <tr>
                            <td>{{$player->name}}</td>
                            <td>{{$position}}</td>
                            <td>{{$countIn}}</td>
                            <td>{{$countOut}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center">No players in club</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div id='panel-week'>

                    <div class="form_wrap border" style="max-width:100%;">
                        <div class="table-responsive">

                            <table id="" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Points this week </th>
                                        <th>Match Start</th>
                                        <th>60 Mins</th>
                                        <th>Clean Sheet</th>
                                        <th>Goals Scored</th>
                                        <th>Assists</th>
                                        <th>Goals Conceded</th>
                                        <th>Penalty Save</th>
                                        <th>Penalty Miss</th>
                                        <th>Yello Cards</th>
                                        <th>Red Card</th>
                                        <th>Best Player 1</th>
                                        <th>Best Player 2</th>
                                        <th>Best Player 3</th>
                                        <th>Hattrick</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($players as $player)
                                    @php
                                    $gameweek = getPlayerGameWeekData($player->id, $week);

                                    $positionArray = [
                                    'FOR'=> 'A',
                                    'MID' => 'M',
                                    'DEF' => 'D',
                                    'GK'=> 'G'];

                                    $position = $player->position;
                                    if(array_key_exists($position, $positionArray)){
                                    $position = $positionArray[$position];
                                    }

                                    @endphp
                                    <tr>
                                        <td>{{$player->name}} <span style="color: #7ab428;">({{$position}})</span></td>
                                        <td><?php echo Form::number('form[' . $player->id . '][points]', (!empty($gameweek) ? $gameweek->points : ''), ['class' => '', 'disabled' => 'disabled', 'style' => 'width: 50px;']) ?></td>
                                        <td><?php echo Form::checkbox('form[' . $player->id . '][match_start]', 1, (!empty($gameweek) && $gameweek->match_start) ? true : false) ?>
                                        </td>
                                        <td><?php echo Form::checkbox('form[' . $player->id . '][played_for_60_mins]', 1, (!empty($gameweek) && $gameweek->played_for_60_mins) ? true : false) ?>
                                        </td>
                                        <td><?php echo Form::checkbox('form[' . $player->id . '][clean_sheet]', 1, (!empty($gameweek) && $gameweek->clean_sheet) ? true : false) ?>
                                        </td>
                                        <td><?php echo Form::number('form[' . $player->id . '][number_of_goals]', (!empty($gameweek) ? $gameweek->number_of_goals : ''), ['class' => '']) ?>
                                        </td>
                                        <td><?php echo Form::number('form[' . $player->id . '][number_of_assists]', (!empty($gameweek) ? $gameweek->number_of_assists : ''), ['class' => '']) ?>
                                        </td>
                                        <td><?php echo Form::number('form[' . $player->id . '][number_of_goals_conceded]', (!empty($gameweek) ? $gameweek->number_of_goals_conceded : ''), ['class' => '']) ?>
                                        </td>
                                        <td><?php echo Form::number('form[' . $player->id . '][penalty_save]', (!empty($gameweek) ? $gameweek->penalty_save : ''), ['class' => '']) ?>
                                        </td>
                                        <td><?php echo Form::number('form[' . $player->id . '][penalty_miss]', (!empty($gameweek) ? $gameweek->penalty_miss : ''), ['class' => '']) ?>
                                        </td>
                                        <td><?php echo Form::number('form[' . $player->id . '][number_of_yellow_cards]', (!empty($gameweek) ? $gameweek->number_of_yellow_cards : ''), ['class' => '']) ?>
                                        </td>
                                        <td><?php echo Form::checkbox('form[' . $player->id . '][number_of_red_cards]', 1, (!empty($gameweek) && $gameweek->number_of_red_cards) ? true : false) ?>
                                        </td>
                                        <td><?php echo Form::checkbox('form[' . $player->id . '][best_player]', 1, (!empty($gameweek) && $gameweek->best_player) ? true : false) ?>
                                        </td>
                                        <td><?php echo Form::checkbox('form[' . $player->id . '][second_best_player]', 1, (!empty($gameweek) && $gameweek->second_best_player) ? true : false) ?>
                                        </td>
                                        <td>
                                            <?php echo Form::checkbox('form[' . $player->id . '][third_best_player]', 1, (!empty($gameweek) && $gameweek->third_best_player) ? true : false) ?>
                                        </td>
                                        <td>
                                            <?php echo Form::checkbox('form[' . $player->id . '][hattrick]', 1, (!empty($gameweek) && $gameweek->hattrick) ? true : false) ?>
                                            <?php echo Form::hidden('form[' . $player->id . '][week_number]', $week, ['class' => '']) ?>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="14" class="text-center">No players in club</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-12 text-right padding0">
                                <button type="submit" class="btn btn-preview mt15 pull-right">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                @else
                <p>Game week data added already</p>
                @endif
            </div>
        </div>
    </div>
</section>
<link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">

{{-- <script src="https://code.jquery.com/jquery-3.3.1.js"></script> --}}
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function () {

        $('#gameweek_table').DataTable({
            dom: 'Bfrtip',
            buttons: [
            {
                extend: 'excelHtml5',
                title: 'Game Week export'
            },
            {
                extend: 'pdfHtml5',
                title: 'Game Week export'
            },
            {
                extend: 'csv',
                title: 'Game Week export'
            },
            {
                extend: 'copy',
                title: 'Game Week export'
            },
            {
                extend: 'print',
                title: 'Game Week export'
            }
            ],
            // buttons: [
            //     'copy', 'csv', 'excel', 'pdf', 'print'
            // ],
            searching: false,
            paging: false,
            info: false,
        });


        $(document).on('click', '.gameweek_button', function (event) {
            var target = $(this).attr('data-target');
            if (target !== '') {
                window.location.href = target;
            }
        });


        $("#week_date").on('change', function () {

            // alert(this.value);
            var week_num = this.value;
            var id = $('#clubID').val();
            $('.fa-spinner').show();

            $.ajax({

                url: "/admin/gameweek-detail/" + week_num + "/" + id,
                type: 'Get',
                success: function (data) {
                    // alert('Data: '+data);
                    $('#panel-week').html('');
                    $('#panel-week').html(data);
                    $('.fa-spinner').hide();

                },
                error: function (request, error) {
                    alert("Request: " + JSON.stringify(request));
                }
            });
        });

        var clubIdd = $("#giveId a.active").attr('id');
        $('#clubID').val(clubIdd);

    });

</script>
@endsection
