@extends('admin.layouts.app')
@section('content')
<?php global $ids;?>
<section class="main_wrapper">
    <div class="left-panel-control" id="left-panel-open">
    
        <div id="site-menu">
            <div class="left-panel-heading float-left w-100 left-panel-inner-space mb-10">
                <div class="new-sitebox">
                    <a href="{{url('/admin/gameweek')}}" class="btn btn-newdefault"><i class="fas fa-plus-circle"></i> Gameweek Wizard</a>
                </div>
            </div>
           
        </div>
  
    </div>

    <div class="right-panel">
        <h2>Account Dashboard</h2>
        <div class="row">
            
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <a href="{{url('/admin/gameweek')}}" class="dashboard-component-controll">
                    <div class="dashboard-component">
                        <img src="{{asset('assets/images/website-icon.png')}}" />
                        <h4>Game Week</h4>
                        <p>Manage your Game Weeks</p>
                    </div>
                </a>
            </div>
            
            
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <a href="{{url('admin/settings')}}" class="dashboard-component-controll">
                    <div class="dashboard-component">
                        <img src="{{asset('assets/images/domain-icon.png')}}" />
                        <h4>Settings</h4>
                        <p>Manage your Account</p>
                    </div>
                </a>
            </div>
            
            
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
          
                <a href="{{url('/admin/users')}}" class="dashboard-component-controll">
                    <div class="dashboard-component">
                        <img src="{{asset('assets/images/contact-icon.png')}}" />
                        <h4>Users</h4>
                        <p>Manage Users</p>
                    </div>
                </a>
            </div>
        
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <a href="{{url('/admin/leagues')}}" class="dashboard-component-controll">
                    <div class="dashboard-component">
                        <img src="{{asset('assets/images/email-icon.png')}}" />
                        <h4>Leagues</h4>
                        <p>Manage Player Leagues</p>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <a href="{{url('admin/players')}}" class="dashboard-component-controll">
                    <div class="dashboard-component">
                        <img src="{{asset('assets/images/sms-icon.png')}}" />
                        <h4>Players</h4>
                        <p>Manage Players</p>
                    </div>
                </a>
            </div>
           
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6" >
                    <a href="{{url('admin/clubs')}}" class="dashboard-component-controll">
                        <div class="dashboard-component">
                            <img src="{{asset('assets/images/account_pic.jpg')}}" />
                            <h4>Clubs</h4>
                            <p>Manage Clubs</p>
                        </div>
                    </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6" >
                    <a href="{{url('admin/upload-excel')}}" class="dashboard-component-controll">
                        <div class="dashboard-component">
                            <img src="{{asset('assets/images/account_pic.jpg')}}" />
                            <h4>Player Importer</h4>
                            <p>Add Players Via Csv</p>
                        </div>
                    </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6" >
                    <a href="{{url('admin/download-transfers')}}" class="dashboard-component-controll">
                        <div class="dashboard-component">
                        <i class="fas fa-download" style="font-size: 80px;margin: 30px 0 0 0;"></i>
                            <h4>Transfers</h4>
                            <p>Download player transfer CSV</p>
                        </div>
                    </a>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6" >
            </div>
           
            
        </div>
    </div>
</section>
@endsection
