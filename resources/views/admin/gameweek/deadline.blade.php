@extends('admin.layouts.app')
@section('content')
<style>
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
            <div class="left-panel-heading float-left w-100 left-panel-inner-space mb-10">
                @forelse($clubs as $clubb)
                <a href="javascript:;" data-target="{{url('admin/gameweek/'.$clubb->id)}}"
                    class="btn btn-newsite gameweek_button {{!empty($club) && $club->id == $clubb->id ? 'active' : ''}}">{{$clubb->name}}</a>
                @empty
                <p>No clubs</p>
                @endforelse
            </div>

        </div>

    </div>

    <div class="right-panel">
        <h2>Game Week Deadline</h2>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <form name="deadline" action="{{url('admin/gameweek-deadline-save')}}" method="POST">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class='col-sm-4'>
                            <div class="form-group">
                                <label>
                                    Choose Deadline
                                </label>
                                <input type='text' name='deadline'
                                    value="{{($deadline && $deadline->setting_value)?$deadline->setting_value:''}}"
                                    class="form-control" id='datetimepicker' />
                            </div>
                        </div>
                        <div class='col-sm-4'>
                            <div class="form-group">
                                <label>
                                    Choose Current Game Week
                                </label>
                                <?php
$selection = array();
for ($i = 0; $i <= 52; $i++) {
    $selection[] = $i;
}

echo '<select class="form-control"  name="weekNum" id="weekNum">';

foreach ($selection as $key => $selection) {
    $selected = ($weekNum && $weekNum == $key) ? "selected" : "";
    echo '<option '.$selected.' value="' . $key . '">' . $selection . '</option>';
}

echo '</select>';
?>
                            </div>
                        </div>
                        <div class='col-sm-2'>
                            <input type="submit" name="submit" style="margin-top:32px" value="save"
                                class="btn btn-newsite gameweek_button" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function () {
        $(document).on('click', '.gameweek_button', function (event) {
            var target = $(this).attr('data-target');
            if (target !== '') {
                window.location.href = target;
            }
        });
        $(function () {
            $('#datetimepicker').datetimepicker();
        });
    });

</script>
@endsection
