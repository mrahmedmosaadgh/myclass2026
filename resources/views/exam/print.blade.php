<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $examTitle }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const pageNumbers = document.querySelectorAll(".page-number");
            pageNumbers.forEach(function(el) {
                el.textContent = "Page " + (el.dataset.pageNumber || 1);
            });
        });
    </script>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            counter-reset: page;
        }
        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
        }
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
        }
        .page-break {
            page-break-before: always;
            height: 0;
        }
        .question {
            margin-bottom: 18pt;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 6pt;
        }
        .question-content {
            margin-bottom: 8pt;
            white-space: pre-wrap;
        }
        .question-options {
            margin: 6pt 0 8pt;
        }
        .option {
            margin-bottom: 4pt;
        }
        .answer-area {
            border-top: 1px solid #ccc;
            margin-top: 10pt;
            padding-top: 8pt;
        }
        .answer-line {
            border-bottom: 1px solid #ccc;
            height: 18pt;
            margin-bottom: 6pt;
        }
        .section-header {
            margin: 20pt 0 10pt;
        }
        .section-title {
            font-size: 15pt;
            text-decoration: underline;
            font-weight: bold;
        }
        @media print {
            .print-header {
                position: fixed;
                top: 0;
            }
            .print-footer {
                position: fixed;
                bottom: 0;
            }
            .page-number::after {
                content: " " counter(page);
            }
        }
    </style>
</head>
<body>
    @if($printHeader['enabled'] ?? false)
        <div class="print-header">
            @if(($printHeader['mode'] ?? null) === 'html' && !empty($printHeader['html']))
                {!! $printHeader['html'] !!}
            @elseif(($printHeader['mode'] ?? null) === 'image' && !empty($printHeader['imageUrl']))
                <img src="{{ $printHeader['imageUrl'] }}" style="width:100%; height:auto;">
            @endif
        </div>
    @endif

    <div style="height: 140px;"></div>

    @if(!empty($examTitle))
        <h1>{{ $examTitle }}</h1>
    @endif

    @php $globalIndex = 0; @endphp

    @foreach($renderSections as $section)
        @if(($section['pageBreakBefore'] ?? false) === true)
            <div class="page-break"></div>
        @endif

        @if(!empty($section['title']))
            <div class="section-header">
                <div class="section-title">{{ $section['title'] }}</div>
                @if(!empty($section['instructions']))
                    <div>{{ $section['instructions'] }}</div>
                @endif
            </div>
        @endif

        @foreach($section['questions'] as $question)
            @php
                $globalIndex++;
                $qid = (string) ($question['id'] ?? '');
                $prompt = $question['content']['prompt'] ?? '';
                $options = $question['content']['options'] ?? [];
                $questionType = $question['type'] ?? '';
                $marks = $question['marks'] ?? 1;
                $lines = min(4, max(2, (int) $marks));
            @endphp

            @if(!empty($pageBreakLookup[$qid]))
                <div class="page-break"></div>
            @endif

            <div class="question" data-question-id="{{ $qid }}">
                <div class="question-header">
                    <span>Q{{ $globalIndex }}</span>
                    @if($showMarks)
                        <span>{{ $marks }} marks</span>
                    @endif
                </div>

                <div class="question-content">{{ $prompt }}</div>

                @if(!empty($options) && is_array($options))
                    <div class="question-options">
                        @foreach($options as $idx => $option)
                            <div class="option">
                                <strong>{{ chr(65 + (int) $idx) }})</strong>
                                {{ is_scalar($option) ? $option : ($option['text'] ?? '') }}
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($questionType !== 'multiple_choice' && $questionType !== 'true_false')
                    <div class="answer-area">
                        @for($i = 0; $i < $lines; $i++)
                            <div class="answer-line"></div>
                        @endfor
                    </div>
                @endif

                @if($questionSeparator['enabled'] ?? false)
                    @php
                        $lineStyle = $questionSeparator['lineStyle'] ?? 'solid';
                        $color = $questionSeparator['color'] ?? '#1f3a5a';
                    @endphp
                    <div style="margin: 8pt 0; border-bottom: 1pt {{ $lineStyle }} {{ $color }};"></div>
                @endif
            </div>
        @endforeach
    @endforeach

    @if($printFooter['enabled'] ?? false)
        <div class="print-footer">
            @if(($printFooter['mode'] ?? null) === 'html' && !empty($printFooter['html']))
                {!! $printFooter['html'] !!}
            @endif
            @if($printFooter['showPageNumbers'] ?? false)
                <div class="page-number">Page <span class="page-counter"></span></div>
            @endif
        </div>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof renderMathInElement === 'function') {
                renderMathInElement(document.body, {
                    delimiters: [
                        { left: "$$", right: "$$", display: true },
                        { left: "$", right: "$", display: false }
                    ]
                });
            }
        });
    </script>
</body>
</html>
