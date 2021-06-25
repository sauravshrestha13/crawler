<ul>
    <p>The records are exported by dividing into following chunks of CSVs according to various search keys</p>
    @for($a = "aa";$a!="zz";$a++)
        <li><a href="/export?first={{$a}}">{{$a}}</a></li>
    @endfor
</ul>