<?php

require('auth.php');

$current_page = "exercise.php";

ob_start();

?>

<!-- PUT YOUR EXERCISE FORM + TABLE HERE -->
<!--Content -->
                    <div class="row">

                        <!-- LEFT SIDE: CRUD FORM -->
                        <div class="col-md-4">

                            <div class="card mb-4">

                                <div class="card-body">

                                    <h4 class="text-warning mb-4">
                                        <i class="mdi mdi-run-fast"></i>
                                        Add Exercise
                                    </h4>


                                    <form action="add_exercise.php" method="POST">

                                        <div class="form-group">
                                            <label class="text-white">
                                                Activity Type
                                            </label>

                                            <input type="text" name="activity_type" class="form-control"
                                                placeholder="Jogging, Cycling, Gym">
                                        </div>


                                        <div class="form-group">
                                            <label class="text-white">
                                                Duration (minutes)
                                            </label>

                                            <input type="number" name="duration" class="form-control" placeholder="30">
                                        </div>


                                        <div class="form-group">
                                            <label class="text-white">
                                                Calories Burned
                                            </label>

                                            <input type="number" name="calories_burned" class="form-control"
                                                placeholder="200">
                                        </div>


                                        <div class="form-group">

                                            <label class="text-white">
                                                Intensity Level
                                            </label>

                                            <select name="intensity_level" class="form-control">

                                                <option>Low</option>
                                                <option>Medium</option>
                                                <option>High</option>

                                            </select>

                                        </div>


                                        <div class="form-group">

                                            <label class="text-white">
                                                Exercise Date
                                            </label>

                                            <input type="date" name="exercise_date" class="form-control">

                                        </div>


                                        <button type="submit" class="btn btn-warning btn-block">

                                            <i class="mdi mdi-plus"></i>
                                            Add Exercise

                                        </button>


                                    </form>

                                </div>

                            </div>

                        </div>



                        <!-- RIGHT SIDE: RECORD LIST -->
                        <div class="col-md-8">

                            <div class="card">

                                <div class="card-body">

                                    <h4 class="text-white mb-4">
                                        Exercise Records
                                    </h4>


                                    <div class="table-responsive">

                                        <table class="table table-dark table-hover">

                                            <thead>

                                                <tr>

                                                    <th>
                                                        Date
                                                    </th>

                                                    <th>
                                                        Activity
                                                    </th>

                                                    <th>
                                                        Duration
                                                    </th>

                                                    <th>
                                                        Calories
                                                    </th>

                                                    <th>
                                                        Intensity
                                                    </th>

                                                    <th>
                                                        Action
                                                    </th>

                                                </tr>

                                            </thead>


                                            <tbody>


                                                <!-- PHP LOOP HERE -->

                                                <tr>

                                                    <td>
                                                        2026-07-22
                                                    </td>

                                                    <td>
                                                        Jogging
                                                    </td>

                                                    <td>
                                                        30 min
                                                    </td>

                                                    <td>
                                                        250 kcal
                                                    </td>

                                                    <td>
                                                        <span class="text-success">
                                                            Medium
                                                        </span>
                                                    </td>

                                                    <td>

                                                        <a href="view_exercise.php?id=1" class="btn btn-info btn-sm">

                                                            View

                                                        </a>


                                                        <a href="edit_exercise.php?id=1" class="btn btn-warning btn-sm">

                                                            Edit

                                                        </a>


                                                        <a href="delete_exercise.php?id=1"
                                                            class="btn btn-danger btn-sm">

                                                            Delete

                                                        </a>


                                                    </td>


                                                </tr>


                                            </tbody>


                                        </table>


                                    </div>


                                </div>

                            </div>

                        </div>


                    </div>





<?php

$pageContent = ob_get_clean();

include "layout.php";

?>