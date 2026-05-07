<x-filament-panels::page.simple>
    <div class="custom-login-container">
        <style>
            .custom-login-container {
                animation: fadeIn 0.6s ease-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .custom-login-header {
                text-align: center;
                margin-bottom: 2rem;
            }
            .custom-login-header h2 {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }
            .custom-login-header p {
                color: #6b7280;
                font-size: 0.95rem;
            }
            .fi-simple-main {
                border-radius: 16px !important;
                box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15) !important;
            }
        </style>
        
        <div class="custom-login-header">
            <h2>{{ config('app.name', 'Laravel') }}</h2>
            <p>管理员后台登录</p>
        </div>

        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </div>
</x-filament-panels::page.simple>
