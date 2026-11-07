<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Rating;
use App\Models\Support;
use App\Models\Technician;
use App\Models\RatingResult;
use Illuminate\Http\Request;
use App\Models\RatingMeasure;
use Illuminate\Support\Facades\URL;


class SurveyController extends Controller
{
    /*public function surveyForm(Request $request){
        $surveyDone = RatingResult::where('support_id',$request->taskId)->first();
        $message = "Survey already taken for ticket number : ".$request->taskId;
        if($surveyDone){
            return view('survey.done', ['message'=>$message]);
        }
        $support = Support::with('technician', 'task')->where('id',$request->taskId)->get();
        $result['ratings'] = Rating::all();
        $result['ratings_measure'] = RatingMeasure::all();

        return view('survey.index', ['task_id'=>$request->taskId, 'support'=>$support, 'ratings'=>$result['ratings'], 'ratings_measure'=>$result['ratings_measure']]);
    }*/
    public function surveyForm(Request $request){
        /*dd($request);
        // Ensure the request is valid (prevents tampering)
        if (!$request->hasValidSignature()) {
            abort(403, 'Unauthorized access.');
        }*/

        // Check if the survey was already completed
        $surveyDone = RatingResult::where('support_id', $request->taskId)->first();
        if ($surveyDone) {
            return view('survey.done', ['message' => "Survey already taken for ticket number: " . $request->taskId]);
        }

        // Fetch support details
        $support = Support::with('technician', 'task')->where('id', $request->taskId)->get();
        $result['ratings'] = Rating::all();
        $result['ratings_measure'] = RatingMeasure::all();

        return view('survey.index', [
            'task_id' => $request->taskId,
            'support' => $support,
            'ratings' => $result['ratings'],
            'ratings_measure' => $result['ratings_measure']
        ]);
    }

    public function getSurvey(){
        /*$result['rm1r1'] = RatingResult::with('rating', 'rating_measure')->where('rating_measure_id',1)->where('rating_id',1)->count();
        $result['rm1r2'] = RatingResult::with('rating', 'rating_measure')->where('rating_measure_id',1)->where('rating_id',2)->count();
        $result['rm1r2'] = RatingResult::with('rating', 'rating_measure')->where('rating_measure_id',1)->where('rating_id',3)->count();
        $result['rm1r3'] = RatingResult::with('rating', 'rating_measure')->where('rating_measure_id',1)->where('rating_id',4)->count();
        $result['rm1r4'] = RatingResult::with('rating', 'rating_measure')->where('rating_measure_id',1)->where('rating_id',5)->count();
        $result['rm1r5'] = RatingResult::with('rating', 'rating_measure')->where('rating_measure_id',1)->where('rating_id',6)->count();
        */
        //$ratingIds = [1, 2, 3, 4, 5, 6];
        $ratings = Rating::all();
        $ratingMeasures = RatingMeasure::all();
        $ratingIds = Rating::pluck('id')->toArray();
        $result = [];

        if(empty($ratingsIds)){
            return [$result, $ratings, $ratingMeasures];
        }

        $ratingMeasureIds = RatingMeasure::pluck('id')->toArray();
        foreach($ratingMeasureIds as $rm){
        $counts = RatingResult::with('rating', 'rating_measure')
            ->where('rating_measure_id', $rm)
            ->whereIn('rating_id', $ratingIds)
            ->select('rating_id', \DB::raw('count(*) as total'))
            ->groupBy('rating_id')
            ->pluck('total', 'rating_id');

            // Ensure all rating IDs are included, even with 0 counts
            foreach ($ratingIds as $ratingId) {
                $result[$rm][$ratingId] = $counts->get($ratingId, 0);  // Default to 0 if not found
            }
        }
        
        

        return [$result, $ratings, $ratingMeasures];
    }

    public function getSurveyBySupport(){
        $tasks = Task::all();
        $rating_result_unique = RatingResult::with('support')->select('support_id')->distinct()->get(); //get surveys unique id's
        
        /*
        0:support:{id: 101, employee_id: 173, technician_id: 5, task_id: 2, status: 'Completed', …}
            support_id:101
        1:support:{id: 122, employee_id: 173, technician_id: 2, task_id: 1, status: 'Completed', …}
            support_id:122
        2:support:{id: 114, employee_id: 173, technician_id: 4, task_id: 6, status: 'Completed', …}
            support_id:114
        3:support:{id: 125, employee_id: 173, technician_id: 4, task_id: 6, status: 'Completed', …}
            support_id:125
        4:support:{id: 126, employee_id: 185, technician_id: 1, task_id: 6, status: 'Completed', …}
            support_id:126
        */
        //$support = Support::with('task')->where('task_id', 1, 'id')->get();
        if($rating_result_unique->isEmpty()){
            return [$tasks, []];
        }

        $ret = $rating_result_unique[0]['support'];
        
        return [$tasks, $ret['task_id']];
    }
    /**
     * Display a listing of the resource.
     * Route::get('/users', [UserController::class, 'index']);
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *Route::get('/users/create', [UserController::class, 'create']);
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *Route::post('/users', [UserController::class, 'store']);
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request);
        $result = RatingResult::create([
            'support_id' => $request->support_id,
            'rating_id' => $request->satisfactionRating, //for s1, s2.. .. .., s6
            'feedback' => $request->feedback,
            'rating_measure_id' => 0,
        ]);
        if (!$result){
            //return view('survey.done', ['message'=>"There is an error in submitting your response."]);
            return redirect()->route('index')->with('alert', 'There is an error in submitting your response!'); //To avoid duplication of survey
        }
        //return view('survey.done', ['message'=>"Thank you for taking the time to complete the survey. ER"]);
        return redirect()->route('index')->with('alert', 'Thank you for taking the time to complete the survey. Your feedback is greatly appreciated.'); //To avoid duplication of survey
    }

    /**
     * Display the specified resource.
     *Route::get('/users/{id}', [UserController::class, 'show']);
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *Route::get('/users/{id}/edit', [UserController::class, 'edit']);
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *Route::put('/users/{id}', [UserController::class, 'update']);
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *Route::delete('/users/{id}', [UserController::class, 'destroy']);
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
