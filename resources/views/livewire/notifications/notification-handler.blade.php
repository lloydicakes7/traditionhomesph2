<div class="thha-toast-container">
    @foreach ($notifications as $notification)
        <div class="thha-toast" id="toast-{{ $notification['id'] }}" data-notif-id="{{ $notification['id'] }}">
            <div class="thha-toast-inner thha-toast-{{ $notification['type'] }}">
                <div class="thha-toast-icon">
                    <i class="{{ $notification['icon'] }}"></i>
                </div>
                <div class="thha-toast-body">
                    <strong class="thha-toast-title">{{ $notification['title'] }}</strong>
                    <span class="thha-toast-message">{{ $notification['message'] }}</span>
                </div>
                <button type="button" class="thha-toast-close"
                    onclick="thhaDismissToast('{{ $notification['id'] }}')">&times;</button>
            </div>
        </div>
    @endforeach
</div>

@push('css')
    <style>
        .thha-toast-container {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 420px;
            width: calc(100% - 32px);
            pointer-events: none;
        }

        .thha-toast {
            pointer-events: auto;
            animation: thha-slide-in 0.35s ease-out;
        }

        .thha-toast.thha-toast-removing {
            animation: thha-slide-out 0.25s ease-in forwards;
        }

        @keyframes thha-slide-in {
            from {
                transform: translateX(120%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes thha-slide-out {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        .thha-toast-inner {
            display: flex;
            align-items: flex-start;
            background: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.18), 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #6c757d;
            gap: 12px;
        }

        .thha-toast-inner.thha-toast-success { border-left-color: #28a745; }
        .thha-toast-inner.thha-toast-error   { border-left-color: #dc3545; }
        .thha-toast-inner.thha-toast-warning { border-left-color: #ffc107; }
        .thha-toast-inner.thha-toast-info    { border-left-color: #17a2b8; }

        .thha-toast-icon {
            flex-shrink: 0;
            font-size: 22px;
            line-height: 1;
            padding-top: 1px;
        }
        .thha-toast-success .thha-toast-icon { color: #28a745; }
        .thha-toast-error   .thha-toast-icon { color: #dc3545; }
        .thha-toast-warning .thha-toast-icon { color: #ffc107; }
        .thha-toast-info    .thha-toast-icon { color: #17a2b8; }

        .thha-toast-body {
            flex: 1;
            min-width: 0;
        }

        .thha-toast-title {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .thha-toast-message {
            display: block;
            font-size: 13px;
            color: #475569;
            line-height: 1.4;
        }

        .thha-toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            font-size: 20px;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            margin-left: 4px;
            transition: color 0.15s;
        }
        .thha-toast-close:hover { color: #475569; }

        @media (max-width: 576px) {
            .thha-toast-container {
                top: 10px;
                right: 10px;
                width: calc(100% - 20px);
                max-width: none;
            }
            .thha-toast-inner {
                padding: 12px 14px;
            }
            .thha-toast-title { font-size: 13px; }
            .thha-toast-message { font-size: 12px; }
        }
    </style>
@endpush

@push('js')
    <script>
        var thhaTimers = {};

        window.addEventListener('start-notification-timer', function (e) {
            var id = e.detail.id;
            var toast = document.getElementById('toast-' + id);

            if (thhaTimers[id]) {
                clearTimeout(thhaTimers[id]);
            }

            thhaTimers[id] = setTimeout(function () {
                dismissToast(id);
            }, 5000);
        });

        function dismissToast(id) {
            var toast = document.getElementById('toast-' + id);
            if (toast) {
                if (thhaTimers[id]) {
                    clearTimeout(thhaTimers[id]);
                    delete thhaTimers[id];
                }
                toast.classList.add('thha-toast-removing');
                setTimeout(function () {
                    Livewire.emit('removeNotification', id);
                }, 260);
            } else {
                Livewire.emit('removeNotification', id);
            }
        }

        window.thhaDismissToast = function (id) {
            dismissToast(id);
        };
    </script>
@endpush
