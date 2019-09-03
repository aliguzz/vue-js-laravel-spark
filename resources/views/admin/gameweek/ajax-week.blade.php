           
    
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
                                <?php echo Form::hidden('form[clubID]',$clubId, ['id' => 'clubID']) ?>
                                @forelse($players as $player)
                                @php
                                $gameweek = getPlayerGameWeekData($player->id, $week);
                                @endphp
                                <tr>
                                    <td>{{$player->name}}</td>
                                    <td><?php echo Form::number('form['.$player->id.'][points]' , (!empty($gameweek) ? $gameweek->points : ''), ['class' => '','disabled' => 'disabled', 'style' => 'width: 50px;']) ?></td>
                                    <td><?php echo Form::checkbox('form['.$player->id.'][match_start]', 1, (!empty($gameweek) && $gameweek->match_start) ? true : false) ?></td>
                                    <td><?php echo Form::checkbox('form['.$player->id.'][played_for_60_mins]', 1, (!empty($gameweek) && $gameweek->played_for_60_mins) ? true : false) ?></td>
                                    <td><?php echo Form::checkbox('form['.$player->id.'][clean_sheet]', 1, (!empty($gameweek) && $gameweek->clean_sheet) ? true : false) ?></td>
                                    <td><?php echo Form::number('form['.$player->id.'][number_of_goals]', (!empty($gameweek) ? $gameweek->number_of_goals : ''), ['class' => '']) ?></td>
                                    <td><?php echo Form::number('form['.$player->id.'][number_of_assists]', (!empty($gameweek) ? $gameweek->number_of_assists : ''), ['class' => '']) ?></td>
                                    <td><?php echo Form::number('form['.$player->id.'][number_of_goals_conceded]', (!empty($gameweek) ? $gameweek->number_of_goals_conceded : ''), ['class' => '']) ?></td>
                                    <td><?php echo Form::number('form['.$player->id.'][penalty_save]', (!empty($gameweek) ? $gameweek->penalty_save : ''), ['class' => '']) ?></td>
                                    <td><?php echo Form::number('form['.$player->id.'][penalty_miss]', (!empty($gameweek) ? $gameweek->penalty_miss : ''), ['class' => '']) ?></td>
                                    <td><?php echo Form::number('form['.$player->id.'][number_of_yellow_cards]', (!empty($gameweek) ? $gameweek->number_of_yellow_cards : ''), ['class' => '']) ?></td>
                                    <td><?php echo Form::checkbox('form['.$player->id.'][number_of_red_cards]', 1, (!empty($gameweek) && $gameweek->number_of_red_cards) ? true : false) ?></td>
                                    <td><?php echo Form::checkbox('form['.$player->id.'][best_player]', 1, (!empty($gameweek) && $gameweek->best_player) ? true : false) ?></td>
                                    <td><?php echo Form::checkbox('form['.$player->id.'][second_best_player]', 1, (!empty($gameweek) && $gameweek->second_best_player) ? true : false) ?></td>
                                    <td>
                                    <?php echo Form::checkbox('form['.$player->id.'][third_best_player]', 1, (!empty($gameweek) && $gameweek->third_best_player) ? true : false) ?></td>
                                    <td>
                                    <?php echo Form::checkbox('form['.$player->id.'][hattrick]', 1, (!empty($gameweek) && $gameweek->hattrick) ? true : false) ?>
                                    <?php echo Form::hidden('form['.$player->id.'][week_number]', $week, ['class' => '']) ?>
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
                 