<script>
(function (window, document) {
    const DEFAULT_MODEL_URLS = [
        'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model',
        'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights',
        '/face-api-models'
    ];

    class FaceRecognitionModal {
        constructor(config = {}) {
            this.config = { modelUrls: config.modelUrls || DEFAULT_MODEL_URLS };

            this.modalElement   = document.getElementById('faceRecognitionModal');
            this.videoElement   = document.getElementById('faceRecognitionVideo');
            this.canvasElement  = document.getElementById('faceRecognitionCanvas');
            this.statusElement  = document.getElementById('faceRecognitionStatus');
            this.matchInfoEl    = document.getElementById('faceRecognitionMatchInfo');
            this.captureButton  = document.getElementById('faceRecognitionCaptureBtn');
            this.titleElement   = document.getElementById('faceRecognitionModalLabel');
            this.subtitleElement= document.getElementById('faceRecognitionModalSubtitle');

            const bs = window.bootstrap || (typeof bootstrap !== 'undefined' ? bootstrap : null);
            this.bootstrapModal = (this.modalElement && bs && bs.Modal) ? new bs.Modal(this.modalElement) : null;
            
            const jq = window.jQuery || window.$;
            this.isJQueryModal = !this.bootstrapModal && (typeof jq !== 'undefined' && jq.fn && typeof jq.fn.modal !== 'undefined');

            this.stream            = null;
            this.modelsLoaded      = false;
            this.activeOptions     = null;
            this.isAutoDetecting   = false;
            this.captureAttempted  = false;
            this.detectionTimer    = null;

            if (!this.modalElement || !this.videoElement || !this.captureButton || (!this.bootstrapModal && !this.isJQueryModal)) {
                console.error('Face recognition modal markup or Bootstrap is missing.', {
                    modalElement: !!this.modalElement, 
                    videoElement: !!this.videoElement,
                    captureBtn: !!this.captureButton, 
                    bootstrapModal: !!this.bootstrapModal, 
                    jqueryModal: this.isJQueryModal,
                    jqExists: !!jq,
                    jqFnModal: (jq && jq.fn) ? typeof jq.fn.modal : 'N/A'
                });
                throw new Error('Face recognition modal markup or Bootstrap is missing.');
            }

            this.captureButton.addEventListener('click', () => this.captureFace());
            if (this.isJQueryModal) {
                const jq = window.jQuery || window.$;
                jq(this.modalElement).on('hidden.bs.modal', () => this.cleanup());
            } else {
                this.modalElement.addEventListener('hidden.bs.modal', () => this.cleanup());
            }
        }

        /* ─── PUBLIC: open modal ─────────────────────────────────── */
        async open(options = {}) {
            this.activeOptions = {
                title:        options.title        || 'Face Recognition',
                subtitle:     options.subtitle     || 'Align your face inside the camera frame.',
                captureLabel: options.captureLabel || 'Capture Face',
                onCapture:    options.onCapture    || null,
                autoDetect:   options.autoDetect   || false,
            };

            this.captureAttempted = false;
            this.titleElement.textContent    = this.activeOptions.title;
            this.subtitleElement.textContent = this.activeOptions.subtitle;
            this.showMatchInfo('');

            // Auto-detect mode → hide manual button
            if (this.activeOptions.autoDetect) {
                this.captureButton.style.display = 'none';
            } else {
                this.captureButton.style.display = '';
                this.captureButton.textContent = this.activeOptions.captureLabel;
            }

            this.setStatus('Loading face recognition models...', 'info');
            this.setBusy(true);
            
            if (this.isJQueryModal) {
                const jq = window.jQuery || window.$;
                jq(this.modalElement).modal('show');
            } else {
                this.bootstrapModal.show();
            }

            try {
                await this.ensureModels();
                await this.startCamera();

                if (this.activeOptions.autoDetect) {
                    this.setStatus('🔍 Scanning face... Center your face in the oval.', 'info');
                    this.startAutoDetect();
                } else {
                    this.setStatus('Camera ready. Center your face and capture.', 'success');
                }
            } catch (error) {
                this.setStatus(this.getFriendlyErrorMessage(error), 'danger');
            } finally {
                this.setBusy(false);
            }
        }

        /* ─── Model loading ──────────────────────────────────────── */
        async ensureModels() {
            if (this.modelsLoaded) return;
            if (!window.faceapi) throw new Error('face-api.js failed to load.');

            let lastError = null;
            for (const url of this.config.modelUrls) {
                try {
                    await Promise.all([
                        window.faceapi.nets.ssdMobilenetv1.loadFromUri(url),
                        window.faceapi.nets.faceLandmark68Net.loadFromUri(url),
                        window.faceapi.nets.faceRecognitionNet.loadFromUri(url),
                    ]);
                    this.modelsLoaded = true;
                    return;
                } catch (e) { lastError = e; }
            }
            throw new Error(lastError?.message || 'Unable to load face recognition models.');
        }

        /* ─── Camera ─────────────────────────────────────────────── */
        async startCamera() {
            this.stopCamera();
            this.stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            });
            this.videoElement.srcObject = this.stream;
            await new Promise(resolve => {
                this.videoElement.onloadedmetadata = () =>
                    this.videoElement.play().then(resolve).catch(resolve);
            });
        }

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
            if (this.videoElement) {
                this.videoElement.pause();
                this.videoElement.srcObject = null;
            }
        }

        /* ─── Auto-detect loop ───────────────────────────────────── */
        startAutoDetect() {
            this.isAutoDetecting  = true;
            this.captureAttempted = false;
            this._runDetectionLoop();
        }

        _runDetectionLoop() {
            if (!this.isAutoDetecting || this.captureAttempted) return;

            window.faceapi
                .detectSingleFace(
                    this.videoElement,
                    new window.faceapi.SsdMobilenetv1Options({ minConfidence: 0.55 })
                )
                .withFaceLandmarks()
                .withFaceDescriptor()
                .then(detection => {
                    if (!this.isAutoDetecting || this.captureAttempted) return;

                    if (detection?.descriptor) {
                        this.captureAttempted = true;
                        this.isAutoDetecting  = false;
                        clearTimeout(this.detectionTimer);
                        this.setStatus('✅ Face detected! Verifying...', 'success');
                        this._performCapture(detection);
                    } else {
                        // No face yet — retry after 400 ms
                        this.detectionTimer = setTimeout(() => this._runDetectionLoop(), 400);
                    }
                })
                .catch(() => {
                    if (this.isAutoDetecting) {
                        this.detectionTimer = setTimeout(() => this._runDetectionLoop(), 600);
                    }
                });
        }

        stopAutoDetect() {
            this.isAutoDetecting = false;
            clearTimeout(this.detectionTimer);
            this.detectionTimer = null;
        }

        /* ─── Capture helpers ────────────────────────────────────── */
        async _performCapture(detection) {
            if (!this.activeOptions?.onCapture) return;

            const descriptor = Array.from(detection.descriptor);
            const canvas     = document.createElement('canvas');
            canvas.width     = this.videoElement.videoWidth;
            canvas.height    = this.videoElement.videoHeight;
            canvas.getContext('2d').drawImage(this.videoElement, 0, 0);
            const imageDataUrl = canvas.toDataURL('image/jpeg');

            try {
                await this.activeOptions.onCapture(descriptor, this, imageDataUrl);
            } catch (error) {
                const msg = this.getFriendlyErrorMessage(error);
                this.setStatus('❌ ' + msg, 'danger');

                // Auto-detect mode: retry after 2 s
                if (this.activeOptions?.autoDetect) {
                    setTimeout(() => {
                        const jq = window.jQuery || window.$;
                        const isShown = this.isJQueryModal ? jq(this.modalElement).hasClass('show') : this.bootstrapModal._isShown;
                        if (!isShown) return;
                        this.captureAttempted = false;
                        this.setStatus('🔍 Scanning face... Try again.', 'info');
                        this.startAutoDetect();
                    }, 2500);
                }
            }
        }

        /* Manual capture (button click) */
        async captureFace() {
            if (!this.activeOptions?.onCapture) return;

            this.setBusy(true);
            this.setStatus('Capturing face...', 'info');

            try {
                const detection = await window.faceapi
                    .detectSingleFace(this.videoElement, new window.faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection?.descriptor) {
                    throw new Error('No face detected. Please look directly at the camera and try again.');
                }

                await this._performCapture(detection);
            } catch (error) {
                this.setStatus(this.getFriendlyErrorMessage(error), 'danger');
            } finally {
                this.setBusy(false);
            }
        }

        /* ─── Error messages ─────────────────────────────────────── */
        getFriendlyErrorMessage(error) {
            const raw  = (error?.message || '').toLowerCase();
            const name = (error?.name    || '').toLowerCase();

            if (raw.includes('requested device not found') || name === 'notfounderror')
                return 'No camera found. Please connect or enable a camera and try again.';
            if (raw.includes('permission denied') || raw.includes('permission dismissed') || name === 'notallowederror')
                return 'Camera access blocked. Please allow camera permission in your browser.';
            if (raw.includes('could not start video source') || raw.includes('device in use') || name === 'notreadableerror')
                return 'Your camera is busy in another app. Close other camera apps and retry.';
            if (raw.includes('overconstrained') || name === 'overconstrainederror')
                return 'This camera setup is unsupported. Please try a different camera.';
            if (raw.includes('secure context') || raw.includes('only secure origins') || raw.includes('insecure'))
                return 'Camera needs HTTPS. Please open this page on localhost or a secure URL.';
            if (raw.includes('failed to load face recognition models') || raw.includes('face-api.js failed to load'))
                return 'Face recognition files failed to load. Please refresh and try again.';
            if (raw.includes('no face detected'))
                return 'No face detected. Look directly at the camera and try again.';

            return error?.message || 'Something went wrong. Please try again.';
        }

        /* ─── UI helpers ─────────────────────────────────────────── */
        setBusy(isBusy) {
            if (this.activeOptions?.autoDetect) return; // button hidden; nothing to toggle
            this.captureButton.disabled = isBusy;
            this.captureButton.innerHTML = isBusy
                ? '<span class="spinner-border spinner-border-sm me-2"></span>Processing...'
                : (this.activeOptions?.captureLabel || 'Capture Face');
        }

        setStatus(message, tone = 'info') {
            const cls = { info: 'alert-light border', success: 'alert-success', danger: 'alert-danger', warning: 'alert-warning' }[tone] || 'alert-light border';
            this.statusElement.className = `alert mb-2 ${cls}`;
            this.statusElement.textContent = message;
        }

        showMatchInfo(message) {
            if (!message) {
                this.matchInfoEl.textContent = '';
                this.matchInfoEl.classList.add('d-none');
                return;
            }
            this.matchInfoEl.textContent = message;
            this.matchInfoEl.classList.remove('d-none');
        }

        close() { 
            if (this.isJQueryModal) {
                const jq = window.jQuery || window.$;
                jq(this.modalElement).modal('hide');
            } else {
                this.bootstrapModal.hide(); 
            }
        }

        cleanup() {
            this.stopAutoDetect();
            this.stopCamera();
            this.showMatchInfo('');
            this.captureButton.style.display = '';
            this.captureButton.textContent   = 'Capture Face';
            this.captureButton.disabled      = false;
            this.captureButton.disabled      = false;
        }
    }

    window.OmsaiFaceRecognition = {
        instance: null,
        init(config = {}) {
            if (!this.instance) this.instance = new FaceRecognitionModal(config);
            return this.instance;
        }
    };
})(window, document);
</script>
