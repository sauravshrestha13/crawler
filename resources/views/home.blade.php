
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
    <p>It may take a while to get all the data at once.</p>
    
<a href="/getall">Get All Data</a>
<span>// Directly downloads the csv from server</span>
   
   
<p>Fetch with ajax: <span hidden id="loading">Fetching Chunks.. <span id="chunk">0</span> of <span id="chunklength"> </span></span><p>

<p>First click fetch data and then download CSV</p>
    <a id="fetchData" href="#!">Fetch Data</a>
<a id="download" href="#!" >Download CSV</a>


<table>
    <thead>
        <tr>
        <th>Practice name</th>
        <th>Contact name</th>
        <th>Street</th>
        <th>City </th>
        <th>State</th>
        <th>Post Code</th>
        <th>Country </th>
        <th>Phone</th>
        <th>Funding Scheme</th>
        <th>Area of Practice</th>
        </tr>
    </thead>
    <tbody id="table_body">
    </tbody>
</table>
   
   

<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>


  <script>
      $(document).ready(function(){
          var all_ids=[];
          var all_data=[];

          function spliceIntoChunks(arr, chunkSize) {
                const res = [];
                while (arr.length > 0) {
                    const chunk = arr.splice(0, chunkSize);
                    res.push(chunk);
                }
                return res;
            }

          $('#fetchData').click(function(){
            $("#loading").removeAttr("hidden");
            $("#chunk").html("..");
            $("#chunklength").html("..");


            $.ajax({
                url: "/getids",
                success: function(data){ 
                    all_ids=data['data'];
                    chunks=spliceIntoChunks(all_ids,48);
                    // chunks=chunks.slice(0,2);
                    $("#chunklength").html(chunks.length)
                    chunks.forEach( (element,index) => {
                        $.ajax({
                            url: "/getdata",
                            type:"get",
                            data: {"ids":element},
                            success: function(data){ 
                        $("#chunk").html(index+1);
                        console.log($("#chunk").val()),
                        console.log($("#chunklength").val()),

                        // if($("#chunk").val()==$('#c').val())
                        //         $("#loading").attr("hidden",true);

                                data.data.forEach( e =>{
                                    all_data.push(e);

                                    $('#table_body').append(
                                    "<tr>"+
                                    "<td>"+
                                    e[0]+
                                    "</td>"+
                                    "<td>"+
                                    e[1]+
                                    "</td>"+
                                    "<td>"+
                                    e[2]+
                                    "</td>"+ 
                                    "<td>"+
                                    e[3]+
                                    "</td>"+ "<td>"+
                                    e[4]+
                                    "</td>"+ "<td>"+
                                    e[5]+
                                    "</td>"+ "<td>"+
                                    e[6]+
                                    "</td>"+ "<td>"+
                                    e[7]+
                                    "</td>"+
                                    "<td>"+
                                    e[8]+
                                    "</td>"+ "<td>"+
                                    e[9]+
                                    "</td>"+
                                    "</tr>"
                                    
                                );
                                })
                               
                            },
                            error: function(){
                                // alert("There was an error.");
                            }
                        });


                    });
                },
                error: function(){
                    alert("There was an error.");
                }
            });



          })

          $('#download').click(function(){
            let csvContent = "data:text/csv;charset=utf-8,";

            all_data.forEach(function(rowArray) {
                let row = rowArray.join(",");
                csvContent += row + "\r\n";
            });

            var encodedUri = encodeURI(csvContent);
            window.open(encodedUri);


          })


          
      })
  </script>