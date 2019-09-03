@php
$segment2 = Request::segment(2);
$segment3 = Request::segment(3);
@endphp

<br/>
<br/>
<br/>
<style>.hidden.deleteSubmit{display:none;}</style>

<div class="clearfix"></div>
<div class="block no-space gray">
	<div class="container-fluid">
		<div class="selectors-bar">					
			<section>
				<div class="block no-space gray">
					<div class="container-fluid">
						<div class="selectors-bar">							
							<div class="scrollmenu selectors-container">							
								<ul class="selectors nav nav-tabs">                                           
									<li class="slide"><a  @if($segment2 == 'settings') class="active" @endif  href="{{url('admin/settings')}}"><div class="curve"><div class="icon"><img src="{{asset('frontend/images/domain.svg')}}" /> </div></div> <span>Site Settings</span></a></li>

									<li class="slide"><a  @if($segment2 == 'users') class="active" @endif  href="{{url('admin/users')}}"><div class="curve"><div class="icon"><i class="fa fa-users"></i></div></div> <span>Users</span></a></li>

                                    <li class="slide"><a  @if($segment2 == 'leagues') class="active" @endif  href="{{url('admin/leagues')}}"><div class="curve"><div class="icon"><i class="fa fa-cubes"></i></div></div> <span>Leagues</span></a></li>
                                                                                
									<li class="slide"><a  @if($segment2 == 'players') class="active" @endif  href="{{url('admin/players')}}"><div class="curve"><div class="icon"><i class="fa fa-users"></i></div></div> <span>Players</span></a></li>
																			
									<li class="slide"><a  @if($segment2 == 'clubs') class="active" @endif  href="{{url('admin/clubs')}}"><div class="curve"><div class="icon"><i class="fa fa-quote-left"></i></div></div> <span>Clubs</span></a></li>

									<!--      
									<li class="slide"><a  @if($segment2 == 'subscribers') class="active" @endif  href="{{url('admin/subscribers')}}"><div class="curve"><div class="icon"><img src="{{asset('frontend/images/seo.svg')}}"/></div></div> <span>Subscribers</span></a></li>
																			
									<li class="slide"><a  @if($segment2 == 'help-articles') class="active" @endif  href="{{url('admin/help-articles')}}"><div class="curve"><div class="icon"><i class="fa fa-question-circle"></i></div></div> <span>Help Articles</span></a></li> -->
                                          
								</ul>
									
							</div>	
						</div>				
					</div>
				</div>
			</section>

		</div>
                
	</div>

</div>
