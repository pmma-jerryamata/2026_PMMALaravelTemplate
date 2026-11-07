<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ITSU - SURVEY FORM</title>
        
        <!-- Favicon-->
        <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    </head>

    <body>
        <!-- Responsive navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container px-lg-5">
                <a class="navbar-brand" href="{{ request()->fullUrl() }}"><i class="lni lni-spinner">&nbsp;&nbsp;</i>ITSU - SURVEY FORM</a>
            </div>
        </nav>

        <header>
            <div class="container px-lg-5">
                <div class="p-1 p-lg-1 bg-light rounded-3 text-center">
                    <h1 class="display-8 fw-bold" id='title' name='title'>RATE OUR SERVICE</h1>
                </div>
            </div>
            <script type="text/javascript">
                function checkRadio(){
                    for(let i=1; i<=6; i++){
                        var selected = document.querySelector(
                            'input[name="s' + i + '"]:checked'
                        );
                        if(selected==null){
                            alert("Please provide an answer for Item No. : " + i);
                            document.getElementById(i + "r" + i ).focus();
                            return false;
                        }
                    }
                    return true;
                }
                function checkSelection(){
                    var selected = document.querySelector(
                        'input[name="satisfactionRating"]:checked'
                    );
                    if(selected==null){
                        //document.getElementById('satisfactionRating' ).focus();
                        alert("Kindly select an option for Item No. 1. Thank you!");
                        return false;
                    }
                    //alert(selected.value + " " + document.getElementById('feedback').value);

                    return true;
                }
            </script>
        </header>
        <section class="pt-2">
            <div class="container px-lg-5" align='center'>
                <div class="col-lg-6 col-xxl-5 mb-3">         
                    <div class="card bg-light border-2 h-100 fs-6" align='left'  style="padding:5px;">
                        <div class="fs-6" align='left'><b>Ticket Number: {{ $support[0]->id }}</b></div>
                        <div class="fs-6" align='left'><b>Task: {{ $support[0]['task']->name }}</b> - {{ $support[0]->support_specifics }}</div>
                        <div class="fs-6" align='left'><b>Technician: {{ $support[0]['technician']->name }}</b></div>
                    </div>
                </div>
                <div class="col-lg-6 col-xxl-5 mb-3">    
                    <div class="card bg-light border-2 h-100 fs-6" align='left' style="padding:10px;">
                    <b>1. How would you rate your overall satisfaction with the service we provided for your recent job request?</b>
                <div style="height:5px;font-size:1px;">&nbsp;</div>
                
            
                <form action="{{ route('survey') }}" method="get" onsubmit="return checkSelection()">
                    <input type="hidden" id="task_id" name="support_id" value="{{ $support[0]->id }}">
                    <table>
                    <?php   $idCtr = 1; ?> 
                    @foreach ($ratings as $r)

                    <tr>
                        <td>&nbsp;<input type="radio" id="satisfaction<?php echo $idCtr; ?>" name="satisfactionRating" value=<?php echo $idCtr; ?>></td>
                        <td style="width:95%">
                            <label for="satisfaction<?php echo $idCtr++; ?>">
                                &nbsp;{{ $r->name }}
                            </label>
                        </td>
                    </tr>
                    @endforeach
                    </table>
                        </div>
                    </div>

                    <div style="height:5px;font-size:1px;">&nbsp;</div>

                    <div class="col-lg-6 col-xxl-5 mb-3">    
                    <div class="card bg-light border-2 h-100 fs-6" align='left' style="padding:10px;">
                        <b>2. We value your feedback! Please let us know how we can improve or any thoughts you'd like to share about your experience:</b>
                        <div style="height:10px;font-size:1px;">&nbsp;</div>
                        <textarea id="feedback" name="feedback" style="height: 200px; padding: 5px;"></textarea>
                    </div>    
                    </div>

                    <button class="btn btn-primary btn-lg" type="submit">Submit</button><br /><br />
                </div>
                </form>
            </div>
        </section>


        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; PMMA 2025</p></div>
        </footer>
    </body>
</html>
