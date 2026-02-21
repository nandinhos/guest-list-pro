<div 
    x-data="{
        scanner: null,
        async init() {
            // Aguarda a biblioteca carregar se necessário
            let attempts = 0;
            while (typeof Html5Qrcode === 'undefined' && attempts < 50) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }

            if (typeof Html5Qrcode === 'undefined') {
                console.error('Falha ao carregar a biblioteca de QR Code.');
                return;
            }

            this.start();
        },
        start() {
            if (this.scanner) return;

            this.scanner = new Html5Qrcode('reader');
            
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            this.scanner.start(
                { facingMode: 'environment' }, 
                config, 
                (decodedText) => {
                    $wire.processCheckin(decodedText);
                    
                    if (navigator.vibrate) {
                        navigator.vibrate(100);
                    }

                    // Pausa curta para evitar leituras duplicadas instantâneas
                    this.scanner.pause();
                    setTimeout(() => {
                        if (this.scanner) this.scanner.resume();
                    }, 2000);
                }
            ).catch(err => {
                console.error('Erro ao iniciar câmera:', err);
            });
        },
        stop() {
            if (this.scanner) {
                this.scanner.stop().then(() => {
                    this.scanner = null;
                }).catch(err => console.error('Erro ao parar:', err));
            }
        }
    }"
    x-on:close-modal.window="stop()"
    class="flex flex-col items-center justify-center gap-6"
>
    

    <div class="w-full max-w-sm overflow-hidden rounded-2xl shadow-xl border-4 border-white dark:border-gray-800 bg-black">
        <div id="reader" class="w-full"></div>
    </div>

    <div class="text-center p-4">
        <p class="text-sm text-gray-500 animate-pulse">
            Posicione o QR Code dentro da moldura para validar.
        </p>
    </div>
</div>
