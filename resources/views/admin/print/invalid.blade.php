{{--
    Shown inside the preview frame when a document cannot be drawn yet because
    its form is missing something the document requires. It is a standalone
    page, not an admin layout: it is read inside a small frame, and what matters
    is that the officer sees which field to fill in rather than a blank sheet.

    @var \App\Enums\PrintDocument $document
    @var array<int, string>       $messages
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document->label() }}</title>
</head>
<body style="margin:0; font-family: -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background:#f4f6f9; color:#343a40;">
    <div style="padding:22px 20px;">
        <p style="margin:0 0 6px; font-size:15px; font-weight:600;">
            <i style="color:#dc3545;">&#9888;</i>
            {{ $document->label() }} needs a few details first
        </p>
        <p style="margin:0 0 12px; font-size:13px; color:#6c757d;">
            Fill in the form and choose <strong>Update Preview</strong>.
        </p>

        <ul style="margin:0; padding-left:20px; font-size:13px; line-height:1.7;">
            @foreach ($messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
</body>
</html>
