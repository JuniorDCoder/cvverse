<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $cv->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #111;
        }
        .contact-info {
            font-size: 11px;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            margin-bottom: 10px;
            color: #333;
        }
        .item {
            margin-bottom: 15px;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .item-title {
            font-weight: bold;
            font-size: 14px;
        }
        .item-subtitle {
            font-weight: bold;
            font-style: italic;
        }
        .item-date {
            float: right;
            font-size: 11px;
            color: #666;
        }
        .skills-list, .languages-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .skill-item, .language-item {
            display: inline-block;
            background: #f4f4f4;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 11px;
        }
        p {
            margin: 5px 0;
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="name">{{ $cv->personal_info['full_name'] ?? 'Your Name' }}</div>
        <div class="contact-info">
            @if(isset($cv->personal_info['location'])) {{ $cv->personal_info['location'] }} @endif
            @if(isset($cv->personal_info['email'])) | {{ $cv->personal_info['email'] }} @endif
            @if(isset($cv->personal_info['phone'])) | {{ $cv->personal_info['phone'] }} @endif
            @if(isset($cv->personal_info['linkedin'])) | {{ $cv->personal_info['linkedin'] }} @endif
            @if(isset($cv->personal_info['website'])) | {{ $cv->personal_info['website'] }} @endif
        </div>
    </div>

    @if($cv->summary)
        <div class="section">
            <div class="section-title">Professional Summary</div>
            <p>{{ $cv->summary }}</p>
        </div>
    @endif

    @if(!empty($cv->experience))
        <div class="section">
            <div class="section-title">Work Experience</div>
            @foreach($cv->experience as $exp)
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">{{ $exp['title'] }}</span>
                        <span class="item-date">
                            {{ $exp['start_date'] }} - {{ $exp['current'] ? 'Present' : ($exp['end_date'] ?? '') }}
                        </span>
                    </div>
                    <div class="item-subtitle">{{ $exp['company'] }} @if(isset($exp['location'])) - {{ $exp['location'] }} @endif</div>
                    <p>{!! nl2br(e($exp['description'] ?? '')) !!}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($cv->education))
        <div class="section">
            <div class="section-title">Education</div>
            @foreach($cv->education as $edu)
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">{{ $edu['degree'] }} {{ isset($edu['field']) ? 'in ' . $edu['field'] : '' }}</span>
                        <span class="item-date">
                            {{ $edu['start_date'] ?? '' }} - {{ $edu['end_date'] ?? '' }}
                        </span>
                    </div>
                    <div class="item-subtitle">{{ $edu['institution'] }}</div>
                    @if(isset($edu['gpa']))
                        <p>GPA: {{ $edu['gpa'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($cv->skills))
        <div class="section">
            <div class="section-title">Skills</div>
            <ul class="skills-list">
                @foreach($cv->skills as $skill)
                    <li class="skill-item">{{ $skill }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!empty($cv->languages))
        <div class="section">
            <div class="section-title">Languages</div>
            <ul class="languages-list">
                @foreach($cv->languages as $lang)
                    <li class="language-item">
                        <strong>{{ $lang['language'] }}</strong> 
                        ({{ ucfirst($lang['proficiency']) }})
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if(!empty($cv->projects))
        <div class="section">
            <div class="section-title">Projects</div>
            @foreach($cv->projects as $project)
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">{{ $project['name'] ?? $project['title'] ?? 'Project' }}</span>
                    </div>
                     @if(isset($project['url']))
                        <div class="item-subtitle">{{ $project['url'] }}</div>
                    @endif
                    <p>{!! nl2br(e($project['description'] ?? '')) !!}</p>
                </div>
            @endforeach
        </div>
    @endif

</body>
</html>
