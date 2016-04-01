<?php

namespace App\Http\Controllers\CD\CDCharts;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CD\CDChartQueries\BubbleChartQueryController;
use Lava;
use App\Http\Controllers\CD\ChartController;
/**
 * Purpose: The purpose of this class is to create dynamic bubble charts. It 
 * extends the BubbleChartQueryController which contains the queries that the 
 * class will use.  The query controller will extend an average class
 * ChartManagerController which is responsible for items common to all charts.
 * 
 * @author Jusitn Lutzko
 * 
 * @date March 9, 2016
 * 
 */
class BubbleChartController extends ChartController
{
    public $bubbleChartQueryController;
    
    public function __construct($chartParameters)
    {
        parent::__construct($chartParameters);
        $this->bubbleChartQueryController = new BubbleChartQueryController();
    }
    
    /**
     * Purpose: This is called from the CDDashboard controller and is used to 
     * determine the type of bubble chart to be created. This
     * will be a dynamic chart based on the fields passed in from the form
     * base on the CD dashboard.  If fields are not filled out when 
     * the form is submitted the default chart will be shown.
     * 
     * @return type will return the chart to be displayed.
     * 
     * @author Justin Lutzko and Sean Young
     * 
     * @date March 10, 2016
     */
    public function determineChartToBeMade()
    {
        
        $chart = null;
        
        //Get the comparisons passed in from the chart form on the db controller
        //x-axis
        $comparison1 = $this->chartParameters->comparison1;
        //y-axis
        $comparison2 = $this->chartParameters->comparison2;
        //Bubble radius
        $comparison3 = $this->chartParameters->comparison3;
        
        //If the classSelected is all classes represented by a numeric or text
        //value create the default chart.
        if( $this->chartParameters->classSelected === 1 
                || $this->chartParameters->classSelected === "1" )
        {
            //Here we will determine the queries that will pertain to all 
            //classes.
            
            $dataArray = $this->bubbleChartQueryController->
                    allCoursesComparison($comparison1, 
                    $comparison2);
            
                        //Generate Strings for dynamic labels
            $comp1String = $this->createChartTitles($comparison1);
            
            $comp2String = $this->createChartTitles($comparison2);
            
            $comp3String = $this->createChartTitles($comparison3);
            //Create a dynamic chart, based off of standard information passed 
            //from the CDDashboard controller.
            $chart = $this->createDynamicBubbleChart($dataArray, 
                    $comp1String, $comp2String);
        }
        else
        {
            $comp1String = $this->createChartTitles($comparison1);
            
            $comp2String = $this->createChartTitles($comparison2);
            
            $comp3String = $this->createChartTitles($comparison3);
            
            $courseList = $this->chartParameters->courseList;
            
            $studentList = $this->chartParameters->studentList;
            
            
            //Here we perform queries for individual classes selected.
            $dataArray = $this->bubbleChartQueryController->
                    findTotalsBasedStudentsInCourse($comparison1, $comparison2,
                            $comparison3, $courseList, $studentList);
            
            $chart = $this->createDynamicBubbleChart($dataArray, 
                    $comp1String, $comp2String, $comp3String);
            
        }

        return $chart;
    }
    
    /**
     * Purpose: This function will create a standardized bubble chart.
     * 
     * @param type $dataTable - attribute of the BubbleChartClass that will hold
     * the data/table to be placed in the chart.
     * 
     * @param type $chartID - The HTML ID that will be given to the chart.
     * 
     * @param type $chartTitle - The title at the top of the chart.
     * 
     * @return type Returns a full Column chart with all of the data and labels.
     * Will have two columns
     * 
     * @author Justin Luzko & Sean Young
     * 
     * @date Feb 20, 2016
     * 
     */
    private function createBubbleChart( $dataTable, $chartID, $chartTitle, 
             $comp1Parameter, $comp2Parameter, $chartLimit = 0 )
    {
        //This is where we create the chart and add the data to it.
        $chart = Lava::BubbleChart($chartID, $dataTable, [
            //add title
            'title' => $chartTitle,
                'titleTextStyle' => [
                'color'    => '#008040',
                'fontSize' => 14
            ],
            'hAxis' => ['minValue' => 0, 'title' => $comp1Parameter],
            //set default start value.
            'vAxis' => ['gridlines' => ['count'=> 5 ],'title' => $comp2Parameter,
                'minValue' => 0, 'maxValue' => $chartLimit]
        ]);
        
        return $chart;
    }
    
    /**
     * Purpose: Creates a dynamic array based on parameters passed to the 
     * controller from the form on the CD dashboard controller.
     * 
     * @param type $dataArray - data to be displayed on the chart and placed in
     * the data table.
     * 
     * @param type $comp1String - Name of first heading/label on the chart.
     * 
     * @param type $comp2String - Name of second heading/label on the chart.
     * 
     * @param type $course - $course that we are performing a query for.  Can 
     * also be all courses.
     * 
     * @return type - returns a Bubble chart.
     * 
     * @author Justin Lutzko & Sean Young 
     * 
     * @date Feb 20, 2016
     */
    public function createDynamicBubbleChart($dataArray, $comp1String, 
            $comp2String, $comp3String, $course='All Courses')
    {
      
        //The chart Id this data will have.
        $chartID = 'StudentParam';
        
        //TODO:Fix this as titles should change.
        //This is the title that will appear at the top of the chart.
        $chartTitle = 'Average ' . $comp1String . ', ' . 
                $comp2String . ' And ' .$comp3String ;
        
        //TODO:Change this to a datatable for the bubble chart.
        //Create the rows and columns for the datatable.
        $studentData = Lava::Datatable()
                    ->addStringColumn('Courses')
                    ->addNumberColumn($comp1String)
                    ->addNumberColumn($comp2String)
                    ->addStringColumn('Classes')
                ->addNumberColumn($comp3String)
                    ;
                    //Column labels at bottom of chart. plus columns and labels.
                    foreach( $dataArray as $data)
                    {
                          
                        $studentData->addRow([ 
                            $data['courseID'],
                            $data['param1'], 
                            $data['param2'],
                            
                            $data['courseID'],
                            $data['param3'],
                           ]);
                    }
                    
        
        //Creates a standard CDP column chart with two bars.
        $chart = $this->createBubbleChart($studentData, 
                $chartID, $chartTitle, $comp1String, $comp2String );
       
        //return chart as array.
        return array('bubbleChart'=> $chart);
    }
}