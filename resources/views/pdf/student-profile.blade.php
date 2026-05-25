<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $student->name }} — Student Profile</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 12px;
            background: #ffffff;
        }

        /* ── Header bar ── */
        .header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            padding: 24px 28px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .school-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .report-label {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 2px;
        }

        .code-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* ── Student identity ── */
        .identity {
            margin-top: 18px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 900;
            color: #ffffff;
            flex-shrink: 0;
            line-height: 52px;
            text-align: center;
        }

        .student-name {
            font-size: 22px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.2;
        }

        .student-meta {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
            margin-top: 3px;
        }

        /* ── Body ── */
        .body {
            padding: 24px 28px;
        }

        /* ── Info row ── */
        .info-row {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .info-chip {
            flex: 1;
            background: #f0f7ff;
            border: 1px solid #dbe7f3;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .info-chip-label {
            font-size: 10px;
            font-weight: 700;
            color: #667085;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-chip-value {
            font-size: 13px;
            font-weight: 800;
            color: #172033;
            margin-top: 3px;
        }

        /* ── Section headings ── */
        .section-heading {
            font-size: 11px;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            border-bottom: 2px solid #dbeafe;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        /* ── Group block ── */
        .group-block {
            border: 1px solid #dbe7f3;
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .group-header {
            background: #f0f7ff;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .group-name {
            font-size: 13px;
            font-weight: 800;
            color: #172033;
        }

        .group-meta {
            font-size: 11px;
            color: #667085;
            margin-top: 1px;
        }

        .level-badge {
            background: #dbeafe;
            color: #2563eb;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 10px;
            font-weight: 700;
        }

        /* ── Sessions table ── */
        .sessions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sessions-table th {
            background: #f8fbff;
            color: #667085;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 7px 14px;
            text-align: left;
            border-bottom: 1px solid #dbe7f3;
        }

        .sessions-table td {
            padding: 7px 14px;
            font-size: 11px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .sessions-table tr:last-child td { border-bottom: none; }

        .badge-present {
            display: inline-block;
            background: #ccfbf1;
            color: #0f766e;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-absent {
            display: inline-block;
            background: #fef9c3;
            color: #854d0e;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-pending {
            display: inline-block;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .no-data {
            color: #94a3b8;
            font-size: 11px;
            font-style: italic;
            padding: 10px 14px;
        }

        /* ── Exams table ── */
        .exams-section { margin-top: 24px; }

        .exams-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dbe7f3;
            border-radius: 12px;
            overflow: hidden;
        }

        .exams-table th {
            background: #dbeafe;
            color: #2563eb;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 9px 12px;
            text-align: left;
        }

        .exams-table td {
            padding: 9px 12px;
            font-size: 11px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .exams-table tr:last-child td { border-bottom: none; }
        .exams-table tr:nth-child(even) td { background: #f8fbff; }

        .pct-high { color: #0f766e; font-weight: 800; }
        .pct-mid  { color: #854d0e; font-weight: 800; }
        .pct-low  { color: #be123c; font-weight: 800; }

        /* ── Footer ── */
        .footer {
            margin-top: 28px;
            border-top: 1px solid #dbe7f3;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            color: #94a3b8;
            font-size: 10px;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-top">
        <div>
            <div class="school-name">{{ $schoolName }}</div>
            <div class="report-label">Student Profile Report</div>
        </div>
        @if($student->code)
            <span class="code-badge">{{ $student->code }}</span>
        @endif
    </div>

    <div class="identity">
        <div class="avatar">{{ $initials }}</div>
        <div>
            <div class="student-name">{{ $student->name }}</div>
            <div class="student-meta">
                {{ $student->groups->count() }} {{ Str::plural('group', $student->groups->count()) }} enrolled
                @if($student->date_of_birth)
                    &nbsp;·&nbsp; Born {{ $student->date_of_birth->format('M j, Y') }}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Body -->
<div class="body">

    <!-- Info chips -->
    <div class="info-row">
        <div class="info-chip">
            <div class="info-chip-label">Student Code</div>
            <div class="info-chip-value">{{ $student->code ?: '—' }}</div>
        </div>
        <div class="info-chip">
            <div class="info-chip-label">Date of Birth</div>
            <div class="info-chip-value">{{ $student->date_of_birth?->format('M j, Y') ?: '—' }}</div>
        </div>
        <div class="info-chip">
            <div class="info-chip-label">Phone</div>
            <div class="info-chip-value">{{ $student->phone ?: '—' }}</div>
        </div>
        <div class="info-chip">
            <div class="info-chip-label">Status</div>
            <div class="info-chip-value">{{ $student->is_active ? 'Active' : 'Inactive' }}</div>
        </div>
    </div>

    <!-- Groups & Sessions -->
    <div class="section-heading">Enrolled Groups &amp; Sessions</div>

    @forelse($student->groups as $group)
        <div class="group-block">
            <div class="group-header">
                <div>
                    <div class="group-name">{{ $group->name }}</div>
                    <div class="group-meta">{{ $group->subject ?: 'General' }}</div>
                </div>
                @if($group->level)
                    <span class="level-badge">{{ $group->level }}</span>
                @endif
            </div>

            @php
                $sessions = $group->sessions->sortByDesc('starts_at')->take(10);
            @endphp

            @if($sessions->isNotEmpty())
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Date</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                            @php
                                $status = $session->attendanceRecords
                                    ->where('student_id', $student->id)
                                    ->first()?->status;
                            @endphp
                            <tr>
                                <td>{{ $session->title }}</td>
                                <td>{{ $session->starts_at?->format('M j, Y') ?: '—' }}</td>
                                <td>
                                    @if($status === 'present')
                                        <span class="badge-present">Present</span>
                                    @elseif($status)
                                        <span class="badge-absent">{{ ucfirst($status) }}</span>
                                    @else
                                        <span class="badge-pending">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">No sessions recorded yet.</div>
            @endif
        </div>
    @empty
        <p class="no-data">No groups assigned yet.</p>
    @endforelse

    <!-- Exam Results -->
    <div class="exams-section">
        <div class="section-heading">Exam Results</div>

        @if($examResults->isNotEmpty())
            <table class="exams-table">
                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Group</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($examResults as $result)
                        @php $pct = $result->percentage(); @endphp
                        <tr>
                            <td>{{ $result->exam?->title ?? '—' }}</td>
                            <td>{{ $result->exam?->group?->name ?? '—' }}</td>
                            <td>{{ $result->score }} / {{ $result->exam?->max_score ?? '?' }}</td>
                            <td class="{{ $pct >= 80 ? 'pct-high' : ($pct >= 50 ? 'pct-mid' : 'pct-low') }}">
                                {{ $pct }}%
                            </td>
                            <td>{{ $result->exam?->start_at?->format('M j, Y') ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">No exam results posted yet.</p>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <span>{{ $schoolName }} — Confidential</span>
        <span>Generated {{ now()->format('M j, Y \a\t g:i A') }}</span>
    </div>

</div>
</body>
</html>
