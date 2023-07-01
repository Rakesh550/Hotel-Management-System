@extends('frontlayout')
@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<div class="container my-4">
	<h3 class="mb-3">View Task </h3>

	<div class="container-fluid">

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Task Name</th>
                                            <th>complete ?</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($staff_tasks)
                                        @php $i =0 ; @endphp
                                            @foreach($staff_tasks as $d)
                                            @php $i ++; @endphp
                                            <tr>
                                                <td>{{$i}}</td>
                                                <td>{{$d->task_name}}</td>
                                                <td>
                                                <label class="switch">
                                                <input type="checkbox" id="taskdone{{$d->id}}" value="{{$d->id}}">
                                                <span class="slider round"></span>
                                                </label>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

</div>
@if($staff_tasks)                      
    @foreach($staff_tasks as $d)
        <script>
            $('#taskdone{{$d->id}}').click(function(){
                var taskId = $(this).val();
                var flag = 0;
                if($(this).prop("checked") == true){
                 flag = 1;
                }
                else if($(this).prop("checked") == false){
                    flag = 0;
                }
                
                $.ajax({
                    url:'/task-submit',
                    method:'POST',
                    data:{
                        taskId:taskId,
                        flag:flag,
                        "_token": "{{ csrf_token() }}",
                    },
                    success:function(response){

                    }
                })

            })
        </script>
        @if($d->flag ==1)
        <script>
            $('#taskdone{{$d->id}}').prop('checked', true);
        </script>
        @endif
    @endforeach
@endif

@endsection