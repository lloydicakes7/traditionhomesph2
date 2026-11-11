@push('js')
<script>
    (() => {

        let toolbar = [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['misc', ['undo', 'redo']]
        ];

        let styleTags = ['p', 'h1', 'h2', 'h3'];

        let fontSizes = ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '28'];

        let colors = [
            ['#1B2230', '#6B7280', '#98A0AE', '#E3D7B8', '#EFE6D1', '#F7F2E6', '#FFFEFA', '#000000'],
            ['#C9533C', '#A53F2D', '#F5E0DB', '#1B2A4A', '#0F1A30', '#2E5C8A', '#234666', '#DCE6F1'],
            ['#2E7D5B', '#205C42', '#DCEEE2', '#BC3A2C', '#93291E', '#F7DCD6', '#FF9900', '#C9841E']
        ];

        let buildFields = () => {
            let $ = window.jQuery;
            if (! $) return;

            document.querySelectorAll('.note-editor').forEach((ui) => {
                let source = ui.previousElementSibling;
                if (! source || ! source.hasAttribute('data-rich-text')) {
                    ui.remove();
                }
            });


            document.querySelectorAll('[data-rich-text]').forEach((editor) => {
                if ($(editor).data('summernote') && ! editor.nextElementSibling?.classList.contains('note-editor')) {
                    $(editor).summernote('destroy');
                }

                let inputId = editor.getAttribute('data-rich-text');

                if (! $ || ! $.fn.summernote || ! inputId || $(editor).data('summernote')) {
                    return;
                }

                let height = parseInt(editor.getAttribute('data-rich-text-height'), 10);

                $(editor).summernote({
                    height: height > 0 ? height : 160,
                    placeholder: editor.getAttribute('data-rich-text-placeholder') || '',
                    toolbar: toolbar,
                    styleTags: styleTags,
                    fontSizes: fontSizes,
                    colors: colors,
                    callbacks: {
                        onChange: (contents) => {
                            let input = document.getElementById(inputId);

                            if (input) {
                                input.value = contents;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        }
                    }
                });
            });
        };

        $(() => {
            buildFields();

            if (window.Livewire) {
                Livewire.hook('message.processed', buildFields);
            }
        });
    })();
</script>
@endpush
