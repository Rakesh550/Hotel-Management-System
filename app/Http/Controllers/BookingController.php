<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Booking;
use Stripe;


// use Stripe\Stripe;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings=Booking::all();
        return view('booking.index',['data'=>$bookings]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers=Customer::all();
        return view('booking.create',['data'=>$customers]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'customer_id'=>'required',
            'room_id'=>'required',
            'checkin_date'=>'required',
            'checkout_date'=>'required',
            'total_adults'=>'required',
            'roomprice'=>'required',
        ]);
        

        if($request->ref=='front'){
            $sessionData=[
                'customer_id'=>$request->customer_id,
                'room_id'=>$request->room_id,
                'checkin_date'=>$request->checkin_date,
                'checkout_date'=>$request->checkout_date,
                'total_adults'=>$request->total_adults,
                'total_children'=>$request->total_children,
                'roomprice'=>$request->roomprice,
                'ref'=>$request->ref
            ];
            $room = Room::where('id',$request->room_id)->first();
            $roomType = RoomType::where('id',$room->room_type_id)->first();
            $roomstypes = $room->title.'-'.$roomType->title;
            $amount =($request->roomprice*$request->total_adults);
            $amount_discount = (($request->roomprice*$request->total_adults)*$request->discount)/100; 
            $discount = $amount-$amount_discount;
            session($sessionData);
            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                  'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                      'name' => $roomstypes,
                    ],
                    'unit_amount' => $discount*100,
                  ],
                  'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://127.0.0.1:8000/booking/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://127.0.0.1:8000/laravel-apps/hotelManage/booking/fail',
            ]);
            return redirect($session->url);
        }else{
            $data=new Booking;
            $data->customer_id=$request->customer_id;
            $data->room_id=$request->room_id;
            $data->checkin_date=$request->checkin_date;
            $data->checkout_date=$request->checkout_date;
            $data->total_adults=$request->total_adults;
            $data->total_children=$request->total_children;
            if($request->ref=='front'){
                $data->ref='front';
            }else{
                $data->ref='admin';
            }
            $data->save();
            return redirect('admin/booking/create')->with('success','Data has been added.');
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Booking::where('id',$id)->delete();
        return redirect('admin/booking')->with('success','Data has been deleted.');
    }


    // Check Avaiable rooms
    function available_rooms(Request $request,$checkin_date){
        $arooms=DB::SELECT("SELECT * FROM rooms WHERE id NOT IN (SELECT room_id FROM bookings WHERE '$checkin_date' BETWEEN checkin_date AND checkout_date)");

        $data=[];
        foreach($arooms as $room){
            $roomTypes=RoomType::find($room->room_type_id);
            $data[]=['room'=>$room,'roomtype'=>$roomTypes];
        }

        return response()->json(['data'=>$data]);
    }

    public function front_booking()
    {
        return view('front-booking');
    }

    function booking_payment_success(Request $request){
        \Stripe\Stripe::setApiKey('sk_test_51KrcRwSGMeYATvGRc5KteDyJkkt1sYV2HfoyPV10M5HOu5SwFlV5K2uqgIErLfTq4R8eD8aS8BCHf6XpgW77baWV00XFDQhca1');
        $session = \Stripe\Checkout\Session::retrieve($request->get('session_id'));
        $customer = \Stripe\Customer::retrieve($session->customer);
        if($session->payment_status=='paid'){
            // dd(session('customer_id'));
            $data=new Booking;
            $data->customer_id=session('customer_id');
            $data->room_id=session('room_id');
            $data->checkin_date=session('checkin_date');
            $data->checkout_date=session('checkout_date');
            $data->total_adults=session('total_adults');
            $data->total_children=session('total_children');
            if(session('ref')=='front'){
                $data->ref='front';
            }else{
                $data->ref='admin';
            }
            $data->save();
            return view('booking.success');
        }
    }

    function booking_payment_fail(Request $request){
        return view('booking.failure');
    }
    function GetDiscount(Request $request){
        $discount = 0;
        $roomcount =Room::where('id',$request->roomtypeId)->count();
        if($roomcount > 0)
        {
            $getdiscount =Room::where('id',$request->roomtypeId)->first();
            $room_type_id = $getdiscount->room_type_id;
            $roomtypecount =RoomType::where('id',$room_type_id)->count();
            if($roomtypecount > 0)
            {
               $type=  RoomType::where('id',$room_type_id)->first();
               $discount = $type->discount;
            }
        }
        return $discount;
        
    }
    
}
