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
    @php
        $personalInfo = is_array($cv->personal_info) ? $cv->personal_info : [];
        $experiences = is_array($cv->experience) ? $cv->experience : [];
        $educationItems = is_array($cv->education) ? $cv->education : [];
        $skills = is_array($cv->skills) ? $cv->skills : [];
        $languages = is_array($cv->languages) ? $cv->languages : [];
        $projects = is_array($cv->projects) ? $cv->projects : [];
        $certifications = is_array($cv->certifications) ? $cv->certifications : [];

        $toText = static function ($value, string $default = ''): string {
            if ($value === null) {
                return $default;
            }

            if (is_scalar($value)) {
                return (string) $value;
            }

            if ($value instanceof \Stringable) {
                return (string) $value;
            }

            if (is_array($value)) {
                $parts = [];

                array_walk_recursive($value, static function ($item) use (&$parts): void {
                    if (is_scalar($item)) {
                        $parts[] = (string) $item;
                    }
                });

                return $parts !== [] ? implode(', ', $parts) : $default;
            }

            return $default;
        };

        $toBool = static function ($value): bool {
            if (is_bool($value)) {
                return $value;
            }

            if (is_numeric($value) || is_string($value)) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            return false;
        };
    @endphp

    <div class="header">
        <div class="name">{{ $toText($personalInfo['full_name'] ?? null, 'Your Name') }}</div>
        <div class="contact-info">
            @if(isset($personalInfo['location'])) {{ $toText($personalInfo['location']) }} @endif
            @if(isset($personalInfo['email'])) | {{ $toText($personalInfo['email']) }} @endif
            @if(isset($personalInfo['phone'])) | {{ $toText($personalInfo['phone']) }} @endif
            @if(isset($personalInfo['linkedin'])) | {{ $toText($personalInfo['linkedin']) }} @endif
            @if(isset($personalInfo['website'])) | {{ $toText($personalInfo['website']) }} @endif
        </div>
    </div>

    @if($cv->summary)
        <div class="section">
            <div class="section-title">Professional Summary</div>
            <p>{{ $toText($cv->summary) }}</p>
        </div>
    @endif

    @if(! empty($experiences))
        <div class="section">
            <div class="section-title">Work Experience</div>
            @foreach($experiences as $exp)
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">{{ $toText($exp['title'] ?? null, 'Role') }}</span>
                        <span class="item-date">
                            {{ $toText($exp['start_date'] ?? null) }} - {{ $toBool($exp['current'] ?? false) ? 'Present' : $toText($exp['end_date'] ?? null) }}
                        </span>
                    </div>
                    <div class="item-subtitle">{{ $toText($exp['company'] ?? null) }} @if(isset($exp['location'])) - {{ $toText($exp['location']) }} @endif</div>
                    <p>{!! nl2br(e($toText($exp['description'] ?? null))) !!}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if(! empty($educationItems))
        <div class="section">
            <div class="section-title">Education</div>
            @foreach($educationItems as $edu)
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">{{ $toText($edu['degree'] ?? null) }} {{ isset($edu['field']) ? 'in ' . $toText($edu['field']) : '' }}</span>
                        <span class="item-date">
                            {{ $toText($edu['start_date'] ?? null) }} - {{ $toText($edu['end_date'] ?? null) }}
                        </span>
                    </div>
                    <div class="item-subtitle">{{ $toText($edu['institution'] ?? null) }}</div>
                    @if(isset($edu['gpa']))
                        <p>GPA: {{ $toText($edu['gpa']) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(! empty($skills))
        <div class="section">
            <div class="section-title">Skills</div>
            <ul class="skills-list">
                @foreach($skills as $skill)
                    <li class="skill-item">{{ $toText($skill) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(! empty($languages))
        <div class="section">
            <div class="section-title">Languages</div>
            <ul class="languages-list">
                @foreach($languages as $lang)
                    <li class="language-item">
                        <strong>{{ $toText($lang['language'] ?? null, 'Language') }}</strong>
                        ({{ ucfirst($toText($lang['proficiency'] ?? null, 'basic')) }})
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if(! empty($projects))
        <div class="section">
            <div class="section-title">Projects</div>
            @foreach($projects as $project)
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">{{ $toText($project['name'] ?? ($project['title'] ?? null), 'Project') }}</span>
                    </div>
                    @if(isset($project['url']))
                        <div class="item-subtitle">{{ $toText($project['url']) }}</div>
                    @endif
                    <p>{!! nl2br(e($toText($project['description'] ?? null))) !!}</p>
                    @if(! empty($project['technologies']) && is_array($project['technologies']))
                        <ul class="skills-list" style="margin-top: 5px;">
                            @foreach($project['technologies'] as $tech)
                                <li class="skill-item">{{ $toText($tech) }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(! empty($certifications))
        <div class="section">
            <div class="section-title">Certifications</div>
            @foreach($certifications as $cert)
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">{{ $toText($cert['name'] ?? null, 'Certification') }}</span>
                        @if(isset($cert['date']))
                            <span class="item-date">{{ $toText($cert['date']) }}</span>
                        @endif
                    </div>
                    @if(isset($cert['issuer']))
                        <div class="item-subtitle">{{ $toText($cert['issuer']) }}</div>
                    @endif
                    @if(isset($cert['url']))
                        <p style="font-size: 10px; color: #666;">{{ $toText($cert['url']) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</body>
</html>
