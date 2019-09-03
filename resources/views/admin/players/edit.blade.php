@extends('admin.layouts.app')

@section('content')

@include('admin.settings.subheader') 
<!-- Validation -->
<script src="{{ asset('js/plugins/validation/jquery.validate.min.js')}}"></script>
<link rel="stylesheet" href="{{ asset('css/plugins/select2/select2.css') }}">
<script src="{{ asset('js/plugins/select2/select2.min.js')}}"></script>



<div class="container-fluid">
    <div id="loading" style="display: none;"></div>
    <section class="inner-full-width-panel pr-30">
        <div class="tab-content">
            <div id="menu1" class="right-content-space fix-width">

                <div class="editor-domain-container-heading">
                    <div class="page-header"><h3>{!!$action!!} Players</h3></div>
                </div>
        
        <div class="box">
            <div class="box-content border">
                <form id="user-form" class="form-horizontal form-validate" action="{{url('/admin/players')}}" method="POST" novalidate="novalidate">
                    <div class="form_wrap">
                        <input type="hidden" name="action" value="{!!$action!!}">
                        <input type="hidden" name="id" value="{!!@$player['id']!!}">
                        {{ csrf_field() }}
                        
                        <div class="form-group">
                            <label class="control-label" for="name">Name</label>
                            <div class="">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Enter Player Name" value="{!!@$player['name']!!}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="club" class="control-label">Player Club</label>
                            <div class="">
                                <select name="club" id="club" class='form-control'>
                                    <option disabled selected>Please select Player's club</option>
                                    @foreach($clubs as $club)
                                    <option @if(isset($player['club']) && $club->id == $player['club']) selected @endif value="{!!$club->id!!}">{!!$club->name!!}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        

                        <div class="form-group">
                            <label class="control-label pt_0" for="injured_available">Injured Available</label>
                            <div class="">
                                <input type="radio" name="injured_available" value="1" @if(!isset($player['injured_available']) || $player['injured_available'] == 1) checked @endif /> Yes &nbsp;&nbsp; <input type="radio" name="injured_available" value="0"  @if(isset($player['injured_available']) && $player['injured_available'] == 0) checked @endif /> No
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label pt_0" for="injured_out">Injured Out</label>
                            <div class="">
                                <input type="radio" name="injured_out" value="1" @if(!isset($player['injured_out']) || $player['injured_out'] == 1) checked @endif /> Yes &nbsp;&nbsp; <input type="radio" name="injured_out" value="0"  @if(isset($player['injured_out']) && $player['injured_out'] == 0) checked @endif /> No
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label pt_0" for="missing">Missing</label>
                            <div class="">
                                <input type="radio" name="missing" value="1" @if(!isset($player['missing']) || $player['missing'] == 1) checked @endif /> Yes &nbsp;&nbsp; <input type="radio" name="missing" value="0"  @if(isset($player['missing']) && $player['missing'] == 0) checked @endif /> No
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label pt_0" for="suspended">Suspended</label>
                            <div class="">
                                <input type="radio" name="suspended" value="1" @if(!isset($player['suspended']) || $player['suspended'] == 1) checked @endif /> Yes &nbsp;&nbsp; <input type="radio" name="suspended" value="0"  @if(isset($player['suspended']) && $player['suspended'] == 0) checked @endif /> No
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label" for="cost">Cost</label>
                            <div class="">
                                <input type="text" class="form-control" name="cost" id="cost" placeholder="7.90 write with float value" value="{!!@$player['cost']!!}"/>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="position" class="control-label">Player Position</label>
                            <div class="">
                                <select name="position" id="position" class="form-control">
                                    <option disabled selected>Select Position</option>
                                    <option @if(isset($player['position']) && $player['position'] == 'GK' ) selected @endif value="GK">GoalKeeper</option>
                                    <option @if(isset($player['position']) && $player['position'] == 'DEF' ) selected @endif value="DEF">Defender</option>
                                    <option @if(isset($player['position']) && $player['position'] == 'MID' ) selected @endif value="MID">MidFielders</option>
                                    <option @if(isset($player['position']) && $player['position'] == 'FOR' ) selected @endif value="FOR">Forward</option>
                                    
                                    
                                    
                              
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="points">Points</label>
                            <div class=""><input type="text" class="form-control" name="points" id="points" placeholder="Enter points" value="{!!@$player['points']!!}"/></div>
                        </div>
                        
                        
                        
                        <div class="form-actions text-right">
                            <div class="control-label"></div>
                            <a href="{{url('/admin/players')}}" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-preview">Continue</button>
                        </div>
                    </div>    
                </form>
            </div>
        </div>
            </div>
        </div>
    </section>
</div>

<script>
    $("form").validate({
        rules: {
            name: { lettersonly: true }
            name: { lettersonly: true }
        }
    });
</script>
@endsection