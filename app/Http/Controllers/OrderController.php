<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get all the orders
        $order = Order::all();
        return response()->json(['data' => $order], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        //order form validation
        $this->validate($request, [
            'customer_name' => 'required',
            'customer_email' => 'required|email',
            'customer_phone' => 'required',
            'customer_address' => 'required',
            'amount' => 'required',
            'product_name' => 'required',
            'product_details' => 'required',
        ]);
        $data = $request->all();
        DB::beginTransaction();
        try {
            //create order
            $order = Order::makeOrder($data);
            //make payment to portwallet
            $response = (new OrderPayment())->makePayment($order);
            //update invoice id and url
            $order->invoice_id=$response['data']['invoice_id'];
            $order->invoice_url=$response['data']['action']['url'];
            $order->save();
            DB::commit();
            return response()->json(['success' => true, 'payment' => $response], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'errmsg' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    //Order Payment Status update through PortPos IPN

    public function portposIpn(Request $request)
    {
        $invoiceId = $request->invoice;
        $amount = $request->amount;
        //get the specific order by invoice id
        $order=Order::where('invoice_id',$invoiceId)->first();
        //update order status
        if($order->amount==$amount){
            $order->status='Paid';
        }
        $order->save();

    }

    //order refund

    public  function orderRefund(Request $request)
    {
        $invoiceId = $request->invoice_id;
        $amount=$request->amount;
        //get the specific order by invoice id
        $order =Order::where('invoice_id',$invoiceId)->first();
        //update requested amount is greater then order amount
        if($order->amount<$amount){
            return response()->json(['success' => false, 'msg' =>'Refund amount cant be greater than purchase amount'], 400);
        }
        //refund in portwallet
        $response = (new OrderPayment())->makeRefund($order,$amount);

        return response()->json(['success' => true, 'payment' => $response], 200);
    }
}
