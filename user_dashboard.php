<?php
require_once 'auth.php';
require_once 'database.php';

 $user_id  = (int)$_SESSION['user_id'];
 $today    = date('Y-m-d');
 $curMonth = (int)date('m');
 $curYear  = (int)date('Y');
 $userDisplay = htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']);

function moodColor($mood) {
    return match($mood) {
        'Happy'=>'#4caf50','Excited'=>'#ff9800','Calm'=>'#00bcd4','Neutral'=>'#9e9e9e',
        'Sad'=>'#2196f3','Anxious'=>'#9c27b0','Angry'=>'#f44336', default=>'#607d8b',
    };
}
function moodIcon($mood) {
    return match($mood) {
        'Happy'=>'mdi-emoticon-happy','Excited'=>'mdi-emoticon-excited','Calm'=>'mdi-emoticon-cool',
        'Neutral'=>'mdi-emoticon-neutral','Sad'=>'mdi-emoticon-sad','Anxious'=>'mdi-emoticon-confused',
        'Angry'=>'mdi-emoticon-angry', default=>'mdi-emoticon-neutral',
    };
}
function fmtMoney($v) { return '$' . number_format((float)$v, 2); }

function ringSvg($pct, $color, $size = 72) {
    $r = 30;
    $c = round(2 * M_PI * $r, 1); // 188.5
    $pct = max(0, min(100, (float)$pct));
    $dash = $pct > 0 ? round(($pct / 100) * $c, 1) : 0;
    $gap  = round($c - $dash, 1);
    return "<svg width='$size' height='$size' viewBox='0 0 72 72'>
        <circle cx='36' cy='36' r='$r' fill='none' stroke='rgba(255,255,255,0.06)' stroke-width='5'/>
        <circle cx='36' cy='36' r='$r' fill='none' stroke='$color' stroke-width='5'
            stroke-dasharray='$dash $gap' stroke-linecap='round'
            transform='rotate(-90 36 36)' style='transition:stroke-dasharray 1.2s ease'/>
    </svg>";
}

// ── 1. Exercise ──
  $exGoalRow = $con->query("SELECT * FROM exercise_goals WHERE user_id = $user_id")->fetch_assoc();
 $exerciseGoal = $exGoalRow ? (int)$exGoalRow['target_value'] : 0;
 $goalPeriod  = $exGoalRow ? $exGoalRow['period'] : 'monthly';

// Period range — same logic as exercise.php
if ($goalPeriod === 'weekly') {
    $periodStart = date('Y-m-d', strtotime('monday this week'));
    $periodEnd   = date('Y-m-d', strtotime('sunday this week'));
} else {
    $periodStart = date('Y-m-01');
    $periodEnd   = date('Y-m-t');
}

// Calories in goal period + count
 $exRow = $con->query(
    "SELECT COUNT(*) AS cnt, COALESCE(SUM(calories_burned),0) AS cal
     FROM exercise WHERE user_id=$user_id AND activity_status='Completed'
     AND exercise_date BETWEEN '$periodStart' AND '$periodEnd'"
)->fetch_assoc();
 $exerciseDone = (int)$exRow['cnt'];
 $goalCalories = (int)$exRow['cal'];

 // All-time total calories (for sub text)
 $totalCalories = (int)$con->query(
    "SELECT COALESCE(SUM(calories_burned),0) AS cal
     FROM exercise WHERE user_id=$user_id AND activity_status='Completed'"
)->fetch_assoc()['cal'];

// FIXED: calories vs calorie target (was: count vs calorie target)
 $exercisePct = $exerciseGoal > 0 ? min(100, round(($goalCalories / $exerciseGoal) * 100)) : ($goalCalories > 0 ? 100 : 0);
 $goalLabel = ucfirst($goalPeriod);


// ── 2. Money ──
 $incR = $con->query("SELECT COALESCE(SUM(amount),0) AS t FROM transactions WHERE user_id=$user_id AND type='income' AND MONTH(transaction_date)=$curMonth AND YEAR(transaction_date)=$curYear")->fetch_assoc();
 $expR = $con->query("SELECT COALESCE(SUM(amount),0) AS t FROM transactions WHERE user_id=$user_id AND type='expense' AND MONTH(transaction_date)=$curMonth AND YEAR(transaction_date)=$curYear")->fetch_assoc();
 $incomeTotal  = (float)$incR['t'];
 $expenseTotal = (float)$expR['t'];
 $balance = $incomeTotal - $expenseTotal;
 $moneyPct = $incomeTotal > 0 ? min(100, round(($expenseTotal / $incomeTotal) * 100)) : 0;

// ── 3. Habits ──
 $hTot = (int)$con->query("SELECT COUNT(*) AS c FROM habits WHERE user_id=$user_id AND habit_date='$today'")->fetch_assoc()['c'];
 $hDon = (int)$con->query("SELECT COUNT(*) AS c FROM habits WHERE user_id=$user_id AND habit_date='$today' AND completion_status='Completed'")->fetch_assoc()['c'];
 $habitPct = $hTot > 0 ? round(($hDon / $hTot) * 100) : 0;

// ── 4. Mood ──
 $moodRow = $con->query("SELECT mood_status, diary_date FROM diary WHERE user_id=$user_id ORDER BY diary_date DESC LIMIT 1")->fetch_assoc();
 $latestMood = $moodRow ? $moodRow['mood_status'] : null;

// ── Chart: 7-day exercise (always starts Sunday) ──
 $exChart = [];
 $sunday = date('Y-m-d', strtotime('last Sunday'));
for ($i = 0; $i <= 6; $i++) {
    $d = date('Y-m-d', strtotime("$sunday +$i days"));
    $r = $con->query("SELECT COALESCE(SUM(calories_burned),0) AS cal FROM exercise WHERE user_id=$user_id AND exercise_date='$d' AND activity_status='Completed'")->fetch_assoc();
    $exChart[] = ['label' => date('D', strtotime($d)), 'cal' => (int)$r['cal']];
}

// ── Chart: Weekly habits (always starts Sunday) ──
 $weekHabits = [];
 $sunday = date('Y-m-d', strtotime('last Sunday'));
for ($i = 0; $i <= 6; $i++) {
    $d = date('Y-m-d', strtotime("$sunday +$i days"));
    $t = (int)$con->query("SELECT COUNT(*) AS c FROM habits WHERE user_id=$user_id AND habit_date='$d'")->fetch_assoc()['c'];
    $c = (int)$con->query("SELECT COUNT(*) AS c FROM habits WHERE user_id=$user_id AND habit_date='$d' AND completion_status='Completed'")->fetch_assoc()['c'];
    $weekHabits[] = ['label' => date('D', strtotime($d)), 'total' => $t, 'done' => $c];
}

// ── Chart: Expense categories ──
 $categories = [];
 $catRes = $con->query("SELECT category, SUM(amount) AS t FROM transactions WHERE user_id=$user_id AND type='expense' AND MONTH(transaction_date)=$curMonth AND YEAR(transaction_date)=$curYear GROUP BY category ORDER BY t DESC");
while ($c = $catRes->fetch_assoc()) $categories[] = $c;

// ── Chart: Mood distribution ──
 $moodDist = [];
 $mdRes = $con->query("SELECT mood_status, COUNT(*) AS c FROM diary WHERE user_id=$user_id GROUP BY mood_status ORDER BY c DESC");
while ($m = $mdRes->fetch_assoc()) $moodDist[] = $m;

 $doughnutColors = ['#0090e7','#f6b93b','#4caf50','#e74c3c','#9c27b0','#00bcd4','#ff9800','#e91e63','#795548'];

ob_start();
?>

<style>
    .kpi-card {
        background: #1e2230; border-radius: 14px; padding: 20px;
        display: flex; align-items: center; gap: 18px;
        transition: transform 0.2s, box-shadow 0.2s; height: 100%;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,0.3); }
    .kpi-ring { position: relative; flex-shrink: 0; }
    .kpi-ring-label {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
        font-size: 13px; font-weight: 700; color: #fff;
    }
    .kpi-info { flex: 1; min-width: 0; }
    .kpi-label { font-size: 11px; color: #6c7a89; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
    .kpi-value { font-size: 20px; font-weight: 700; color: #fff; line-height: 1.2; }
    .kpi-sub { font-size: 11px; color: #555d6e; margin-top: 3px; }
    .text-pos { color: #4caf50; } .text-neg { color: #e74c3c; }

    .mood-icon-wrap {
        width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 32px;
    }

    .dash-card { background: #1e2230; border-radius: 14px; padding: 22px; height: 100%; }
    .dash-title {
        font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 14px;
        display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.4px;
    }
    .dash-title i { font-size: 16px; opacity: 0.6; }

    .money-bar { display: flex; height: 6px; border-radius: 3px; overflow: hidden; margin-top: 8px; background: rgba(255,255,255,0.06); }
    .money-bar-income { background: #4caf50; transition: width 1s ease; }
    .money-bar-expense { background: #e74c3c; transition: width 1s ease; }

    .chart-legend { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; justify-content: center; }
    .legend-item { display: flex; align-items: center; gap: 5px; font-size: 11px; color: #8f9bb3; }
    .legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }

    .no-data-msg { display: flex; align-items: center; justify-content: center; height: 100%; color: #555d6e; font-size: 13px; }
</style>

<!-- Greeting -->
<div class="col-12 mb-3">
    <h2 class="text-white font-weight-bold mb-0" style="font-size:21px;">
        <span id="dashGreeting">Welcome back</span>, <?php echo $userDisplay; ?>
    </h2>
    <p class="text-muted mb-0" style="font-size:12px;" id="dashDate"></p>
</div>

<!-- ═══ 4 KPI Cards ═══ -->
<!-- Exercise -->
<div class="col-xl-3 col-lg-6 col-12 mb-4">
    <div class="kpi-card" style="border-left:3px solid #f6b93b;">
        <div class="kpi-ring">
            <?php echo ringSvg($exercisePct, '#f6b93b'); ?>
            <div class="kpi-ring-label"><?php echo $exercisePct; ?>%</div>
        </div>
        <div class="kpi-info">
           <div class="kpi-label"><?php echo $goalLabel; ?> Exercise Goal</div>
            <div class="kpi-value"><?php echo number_format($goalCalories) . ($exerciseGoal > 0 ? ' / ' . number_format($exerciseGoal) : ''); ?></div>
            <div class="kpi-sub"><?php echo $exerciseDone > 0 ? $exerciseDone . ' sessions completed' : 'No exercises yet'; ?></div>
        </div>
    </div>
</div>

<!-- Money -->
<div class="col-xl-3 col-lg-6 col-12 mb-4">
    <div class="kpi-card" style="border-left:3px solid #0090e7;">
        <div class="kpi-ring">
            <?php echo ringSvg(100 - $moneyPct, '#0090e7'); ?>
            <div class="kpi-ring-label" style="font-size:11px;"><?php echo $moneyPct; ?>%</div>
        </div>
        <div class="kpi-info">
            <div class="kpi-label">Balance This Month</div>
            <div class="kpi-value <?php echo $balance >= 0 ? 'text-pos' : 'text-neg'; ?>">
                <?php echo ($balance >= 0 ? '+' : '') . fmtMoney($balance); ?>
            </div>
            <div class="money-bar">
                <div class="money-bar-income" style="width:<?php echo $incomeTotal > 0 ? round(($incomeTotal/($incomeTotal+$expenseTotal+0.01))*100) : 50; ?>%;"></div>
                <div class="money-bar-expense" style="width:<?php echo $incomeTotal > 0 ? round(($expenseTotal/($incomeTotal+$expenseTotal+0.01))*100) : 50; ?>%;"></div>
            </div>
            <div class="kpi-sub" style="margin-top:4px;">
                <span style="color:#4caf50;">+<?php echo fmtMoney($incomeTotal); ?></span>
                <span style="margin:0 4px;">·</span>
                <span style="color:#e74c3c;">-<?php echo fmtMoney($expenseTotal); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Habits -->
<div class="col-xl-3 col-lg-6 col-12 mb-4">
    <div class="kpi-card" style="border-left:3px solid #4caf50;">
        <div class="kpi-ring">
            <?php echo ringSvg($habitPct, '#4caf50'); ?>
            <div class="kpi-ring-label"><?php echo $habitPct; ?>%</div>
        </div>
        <div class="kpi-info">
            <div class="kpi-label">Today's Habits</div>
            <div class="kpi-value"><?php echo "$hDon / $hTot"; ?></div>
            <div class="kpi-sub"><?php echo $hTot > 0 ? "$hDon completed" : 'No habits today'; ?></div>
        </div>
    </div>
</div>

<!-- Mood -->
<div class="col-xl-3 col-lg-6 col-12 mb-4">
    <div class="kpi-card" style="border-left:3px solid <?php echo $latestMood ? moodColor($latestMood) : '#607d8b'; ?>;">
        <div class="mood-icon-wrap" style="background:<?php echo $latestMood ? moodColor($latestMood) . '20' : 'rgba(96,125,139,0.12)'; ?>; color:<?php echo $latestMood ? moodColor($latestMood) : '#607d8b'; ?>;">
            <i class="mdi <?php echo $latestMood ? moodIcon($latestMood) : 'mdi-emoticon-neutral'; ?>"></i>
        </div>
        <div class="kpi-info">
            <div class="kpi-label">Latest Mood</div>
            <div class="kpi-value" style="color:<?php echo $latestMood ? moodColor($latestMood) : '#555d6e'; ?>;">
                <?php echo $latestMood ? htmlspecialchars($latestMood) : 'No entry'; ?>
            </div>
            <div class="kpi-sub"><?php echo $moodRow ? date('M j', strtotime($moodRow['diary_date'])) : 'Write in your diary'; ?></div>
        </div>
    </div>
</div>

<!-- ═══ Charts Row 1 ═══ -->
<div class="col-lg-6 col-12 mb-4">
    <div class="dash-card">
        <div class="dash-title"><i class="mdi mdi-chart-bar" style="color:#f6b93b;"></i> Weekly Exercise</div>
        <div style="position:relative; height:210px;"><canvas id="dashExChart"></canvas></div>
    </div>
</div>

<div class="col-lg-6 col-12 mb-4">
    <div class="dash-card">
        <div class="dash-title"><i class="mdi mdi-chart-timeline-variant" style="color:#4caf50;"></i> Weekly Habits</div>
        <div style="position:relative; height:210px;"><canvas id="dashHabChart"></canvas></div>
    </div>
</div>

<!-- ═══ Charts Row 2 ═══ -->
<div class="col-lg-6 col-12 mb-4">
    <div class="dash-card">
        <div class="dash-title"><i class="mdi mdi-chart-donut" style="color:#0090e7;"></i> Spending Breakdown</div>
        <?php if (!empty($categories)): ?>
            <div style="position:relative; height:190px; margin:0 auto; max-width:200px;"><canvas id="dashExpChart"></canvas></div>
            <div class="chart-legend">
                <?php foreach ($categories as $ci => $cat): ?>
                <div class="legend-item"><span class="legend-dot" style="background:<?php echo $doughnutColors[$ci % count($doughnutColors)]; ?>"></span><?php echo htmlspecialchars($cat['category']); ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data-msg" style="height:190px;">No expenses this month</div>
        <?php endif; ?>
    </div>
</div>

<div class="col-lg-6 col-12 mb-4">
    <div class="dash-card">
        <div class="dash-title"><i class="mdi mdi-emoticon-outline" style="color:<?php echo $latestMood ? moodColor($latestMood) : '#8f9bb3'; ?>"></i> Mood Distribution</div>
        <?php if (!empty($moodDist)): ?>
            <div style="position:relative; height:190px; margin:0 auto; max-width:200px;"><canvas id="dashMoodChart"></canvas></div>
            <div class="chart-legend">
                <?php foreach ($moodDist as $mi => $md): ?>
                <div class="legend-item"><span class="legend-dot" style="background:<?php echo moodColor($md['mood_status']); ?>"></span><?php echo htmlspecialchars($md['mood_status']); ?> (<?php echo (int)$md['c']; ?>)</div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data-msg" style="height:190px;">No mood entries yet</div>
        <?php endif; ?>
    </div>
</div>

<script>
// Greeting uses browser time
(function(){
    var h=new Date().getHours();
    var g=h>=5&&h<12?'Good Morning':h>=12&&h<17?'Good Afternoon':h>=17&&h<21?'Good Evening':'Good Night';
    document.getElementById('dashGreeting').textContent=g;
    document.getElementById('dashDate').textContent=new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'})+' — Here\'s your routine at a glance.';
})();

window.addEventListener('load',function(){
    Chart.defaults.color='#8f9bb3';
    Chart.defaults.borderColor='rgba(255,255,255,0.04)';
    Chart.defaults.font.family="'Segoe UI','Roboto',sans-serif";

    var tt={backgroundColor:'#2a2d3a',titleColor:'#fff',bodyColor:'#c8cdd5',borderColor:'rgba(255,255,255,0.08)',borderWidth:1,cornerRadius:8,padding:10,boxPadding:4};

    // Exercise bar
    new Chart(document.getElementById('dashExChart').getContext('2d'),{
        type:'bar',
        data:{
            labels:<?php echo json_encode(array_column($exChart,'label')); ?>,
            datasets:[{
                label:'Calories',
                data:<?php echo json_encode(array_column($exChart,'cal')); ?>,
                backgroundColor:function(ctx){
                    var a=ctx.chart.chartArea;if(!a)return'#f6b93b';
                    var g=ctx.chart.ctx.createLinearGradient(0,a.bottom,0,a.top);
                    g.addColorStop(0,'rgba(246,185,59,0.3)');g.addColorStop(1,'rgba(246,185,59,0.85)');return g;
                },
                borderRadius:6,borderSkipped:false,barPercentage:0.5,categoryPercentage:0.7
            }]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            animation:{duration:1000,easing:'easeOutQuart'},
            plugins:{legend:{display:false},tooltip:Object.assign({},tt,{callbacks:{label:function(c){return c.parsed.y+' cal';}}})},
            scales:{
                x:{grid:{display:false},ticks:{font:{size:11}}},
                y:{beginAtZero:true,grid:{color:'rgba(255,255,255,0.03)'},ticks:{font:{size:10},callback:function(v){return v+' cal';}}}
            }
        }
    });

    // Habit stacked bar
    new Chart(document.getElementById('dashHabChart').getContext('2d'),{
        type:'bar',
        data:{
            labels:<?php echo json_encode(array_column($weekHabits,'label')); ?>,
            datasets:[
                {label:'Pending',data:<?php echo json_encode(array_map(function($h){return $h['total']-$h['done'];},$weekHabits)); ?>,backgroundColor:'rgba(255,255,255,0.06)',borderRadius:4,borderSkipped:false,barPercentage:0.5,categoryPercentage:0.7},
                {label:'Done',data:<?php echo json_encode(array_column($weekHabits,'done')); ?>,backgroundColor:'rgba(76,175,80,0.7)',borderRadius:4,borderSkipped:false,barPercentage:0.5,categoryPercentage:0.7}
            ]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            animation:{duration:1000,easing:'easeOutQuart'},
            plugins:{legend:{position:'top',align:'end',labels:{boxWidth:10,boxHeight:10,borderRadius:2,useBorderRadius:true,padding:12,font:{size:11}}},tooltip:tt},
            scales:{
                x:{stacked:true,grid:{display:false},ticks:{font:{size:11}}},
                y:{stacked:true,beginAtZero:true,grid:{color:'rgba(255,255,255,0.03)'},ticks:{font:{size:10},stepSize:1,callback:function(v){return Number.isInteger(v)?v:'';}}}
            }
        }
    });

    // Expense doughnut
    <?php if(!empty($categories)): ?>
    new Chart(document.getElementById('dashExpChart').getContext('2d'),{
        type:'doughnut',
        data:{
            labels:<?php echo json_encode(array_column($categories,'category')); ?>,
            datasets:[{data:<?php echo json_encode(array_map(function($c){return(float)$c['t'];},$categories)); ?>,backgroundColor:<?php echo json_encode(array_slice($doughnutColors,0,count($categories))); ?>,borderWidth:0,hoverOffset:6}]
        },
        options:{
            responsive:true,maintainAspectRatio:false,cutout:'65%',
            animation:{duration:800,easing:'easeOutQuart'},
            plugins:{legend:{display:false},tooltip:Object.assign({},tt,{callbacks:{label:function(c){var t=c.dataset.data.reduce(function(a,b){return a+b;},0);var p=t>0?((c.parsed/t)*100).toFixed(0):0;return c.label+': $'+c.parsed.toFixed(2)+' ('+p+'%)';}}})}
        }
    });
    <?php endif; ?>

    // Mood doughnut
    <?php if(!empty($moodDist)): ?>
    new Chart(document.getElementById('dashMoodChart').getContext('2d'),{
        type:'doughnut',
        data:{
            labels:<?php echo json_encode(array_map(function($m){return $m['mood_status'];},$moodDist)); ?>,
            datasets:[{data:<?php echo json_encode(array_map(function($m){return(int)$m['c'];},$moodDist)); ?>,backgroundColor:<?php echo json_encode(array_map(function($m){return moodColor($m['mood_status']);},$moodDist)); ?>,borderWidth:0,hoverOffset:6}]
        },
        options:{
            responsive:true,maintainAspectRatio:false,cutout:'65%',
            animation:{duration:800,easing:'easeOutQuart'},
            plugins:{legend:{display:false},tooltip:Object.assign({},tt,{callbacks:{label:function(c){return c.label+': '+c.parsed+' entries';}}})}
        }
    });
    <?php endif; ?>

    // Animate rings from 0
    document.querySelectorAll('.kpi-ring circle:nth-child(2)').forEach(function(c){
        var orig=c.getAttribute('stroke-dasharray');
        c.setAttribute('stroke-dasharray','0 188.5');
        requestAnimationFrame(function(){requestAnimationFrame(function(){c.setAttribute('stroke-dasharray',orig);});});
    });
});
</script>

<?php
 $pageContent = ob_get_clean();
 $current_page = basename(__FILE__);
require_once 'layout.php';