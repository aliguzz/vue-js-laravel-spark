
<header>
    <div class="header-left left-panel-width-control">
        <div class="logo">
            <a href="{{url('admin/dashboard')}}" title=""><img src="{{asset('frontend/images/short-logo.svg')}}" alt="" /></a>
            <svg width="466" height="603" viewbox="0 0 100 100" preserveAspectRatio="none">
            <path d="M0,0 L100,0 C15,50 50,100 0,100z"/>
            </svg>
        </div>
        <!-- Logo -->
        <div class="header-dropdown">
            <!-- <div class="dropdown">
                <span class="selected-value dropbtn" id="dropdownMenu2" data-toggle="dropdown">Wizard <i class="fas fa-angle-down"></i> </span>
                <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                    <div class="top-head">Add Player </div>
                    <div id="custom-search-input" style="display: none; ">
                        <div class="input-group col-md-12" >
                            <input type="text" class="form-control" placeholder="Search">
                            <span class="input-group-btn">
                                <button class="btn btn-danger" type="button">
                                    <span class=" fas fa-search"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="selectsite">
                            
                              
                            <div class="site-button" id="create_site">
                                <a>
                                    <button type="button" class="btn btn-danger"><i class="fas fa-plus-circle"></i> Player Wizard</button></a>
                            </div>
                            
                           
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
        <!-- Header Dropdown -->
    </div>
    @php $user = Auth::user();  @endphp
    <div class="" style="margin-left: 10px;">
        <div class="header-dropdown">
            <div class="dropdown">
                <span class="selected-value dropbtn" id="dropdownMenu2" data-toggle="dropdown">{{ $user->name }}<i class="fas fa-angle-down"></i> </span>
                <div class="dropdown-menu profile_DropMenu" aria-labelledby="dropdownMenu2">
                    <div class="top-head">Profile</div>
                    <div class="selectsite">
                        <article style="margin: 10px 10px;">
                            <div class="left-box">
                                <img class="img-fluid" src="{{asset('frontend/images/avt.png')}}">
                            </div>
                            <div class="right-box">
                                <a href="{!!url('admin/logout')!!}">
                                    <span class="">Logout</span>
                                </a>
                                <a href="{!!url('admin/my-profile')!!}">
                                    <span class="">Edit Profile</span>
                                </a>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>

        <nav class="navbar fixed-top fixed-top-custom-icon navbar-expand-sm navbar-light">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse">☰</button> 
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav ml-auto header-icons">

                    <a class="icon tooltipp" href="{{url('/admin')}}" title="">
                        <span class="tooltiptext">Dashboard</span><i class="fa fa-user"></i></a>  
    
                    <a class="icon tooltipp" href="{{url('/admin/settings')}}" title="">
                        <span class="tooltiptext">Settings</span><img src="{{asset('assets/images/admincog.svg')}}" height="30" width="30" class="svg2" /></a>
                       
                    <a class="icon tooltipp" href="{{url('/admin/users')}}" title="">
                        <span class="tooltiptext">Users</span><i class="fa fa-users"></i>
                    </a>  

                    <a class="icon tooltipp" href="{{url('/admin/leagues')}}" title="">
                        <span class="tooltiptext">Leagues</span><i class="fa fa-cubes"></i>
                    </a>
                   
                    <a class="icon tooltipp" href="{{url('/admin/players')}}" title="">
                        <span class="tooltiptext">Players</span><i class="fa fa-users"></i>
                    </a>
                   
                    <a class="icon tooltipp" href="{{url('/admin/clubs')}}" title="">
                        <span class="tooltiptext">Clubs</span><i class="fa fa-quote-left"></i>
                    </a>
                   
                   
                   
                   
                    <!-- Fancy Button -->								
                </ul>
            </div>
        </nav>

    </div>
</header>
