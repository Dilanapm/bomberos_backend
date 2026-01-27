<div>
    <h1>Administrador de passkeys</h1>

    <p>Usuario: {{ auth()->user()->email }}</p>

    <button type="button" id="btn-register-passkey">
        Registrar nueva Passkey
    </button>

    <p id="passkey-status"></p>

    @if (session('status'))
        <p style="margin-top:10px;">{{ session('status') }}</p>
    @endif


    <hr>

    <h2>Credenciales registradas</h2>

    @if (isset($credentials) && $credentials->count())
        <ul>
            @foreach ($credentials as $c)
                <li style="margin-bottom:14px;">
                    <div>
                        <strong>ID:</strong> {{ $c->id }}
                        @if (isset($c->created_at))
                            <span> — <small>creada: {{ $c->created_at }}</small></span>
                        @endif
                        @if ($c->disabled_at)
                            <span style="color:red;">(REVOCADA)</span>
                        @endif
                    </div>

                    <div style="margin-top:6px;">
                        <label>Alias:</label>
                        <input type="text" wire:model.defer="alias.{{ $c->id }}"
                            placeholder="Ej: Laptop oficina">
                        @error('alias.' . $c->id)
                            <span style="color:red;">{{ $message }}</span>
                        @enderror

                        <button type="button" wire:click="saveAlias('{{ $c->id }}')">
                            Guardar alias
                        </button>

                        <button type="button" wire:click="revoke('{{ $c->id }}')"
                            onclick="return confirm('¿Revocar esta passkey? Ya no servirá para login.')">
                            Revocar
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p>No tienes passkeys registradas todavía.</p>
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (() => {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;

            const _fetch = window.fetch;
            window.fetch = (input, init = {}) => {
                init.headers = new Headers(init.headers || {});
                init.headers.set('X-CSRF-TOKEN', token);
                init.headers.set('X-Requested-With', 'XMLHttpRequest');
                return _fetch(input, init);
            };
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-register-passkey');
            const status = document.getElementById('passkey-status');

            btn?.addEventListener('click', async () => {
                status.textContent = '';

                if (typeof Webpass === 'undefined' || Webpass.isUnsupported()) {
                    alert("Tu navegador/dispositivo no soporta Passkeys/WebAuthn.");
                    return;
                }

                status.textContent = 'Abriendo registro…';

                const {
                    success,
                    error
                } = await Webpass.attest(
                    "/admin/passkeys/options",
                    "/admin/passkeys"
                );

                if (!success) {
                    status.textContent = '';
                    alert(error ?? "No se pudo registrar la passkey.");
                    return;
                }

                status.textContent = 'Passkey registrada. Recargando…';
                window.location.reload();
            });
        });
    </script>
</div>
