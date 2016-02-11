@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="panel-heading">
                <h3 class="panel-title">
                 <i class="fa fa-bar-chart-o fa-fw"></i>Student Activities</h3>

            </div>
            @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
            @endif       
    @if( isset($studentActivities) && count($studentActivities) > 0 )

            @foreach ($errors->all() as $error)
               <p class="alert alert-danger">{{ $error }}</p>
            @endforeach
            <!--This is  a for each loop that will iterate through all of 
            the activities a user has tied to it's name/id-->
            @foreach($studentActivities as $studAct)
                <h3 class="panel-title">
                <i class="fa fa-bar-chart-o fa-fw"></i>{{$studAct['courseID']}}</h3>
                    @include('student.activity')
            @endforeach
    @else
            
        <p> No activities</p>
             
    @endif      
            </div>
        </div>
    </div>
</div>
@endsection
