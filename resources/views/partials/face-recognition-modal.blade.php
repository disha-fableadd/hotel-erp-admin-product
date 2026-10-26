<style>
    /* Force fixed modal overlay to prevent bottom-left glitch */
    #faceRecognitionModal.modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 99999 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(15, 23, 42, 0.6) !important;
        backdrop-filter: blur(4px);
        display: none; /* hidden by default, JS will add display: block */
    }

    #faceRecognitionModal .modal-dialog {
        max-width: 760px;
        margin: 1.75rem auto !important;
        display: flex !important;
        align-items: center !important;
        min-height: calc(100vh - 3.5rem) !important;
        pointer-events: none;
    }

    #faceRecognitionModal .modal-content {
        pointer-events: auto;
        width: 100%;
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 159, 67, 0.16), transparent 28%),
            linear-gradient(180deg, #fffaf5 0%, #ffffff 36%);
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.28);
    }

    #faceRecognitionModal .modal-header {
        padding: 22px 24px 10px;
        background: transparent;
    }

    #faceRecognitionModal .modal-body {
        padding: 8px 24px 18px;
    }

    #faceRecognitionModal .modal-footer {
        padding: 0 24px 24px;
        gap: 12px;
    }

    .face-modal-heading {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .face-modal-icon {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #ff9f43, #f97316);
        box-shadow: 0 12px 24px rgba(249, 115, 22, 0.28);
        flex-shrink: 0;
    }

    .face-modal-title {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: #172033;
        letter-spacing: -0.02em;
    }

    .face-modal-subtitle {
        margin: 4px 0 0;
        color: #5b6476;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .face-modal-tips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 16px 0 18px;
    }

    .face-tip-chip {
        border: 1px solid #fed7aa;
        background: rgba(255, 255, 255, 0.92);
        color: #9a5b13;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
    }

    .face-camera-shell {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        background:
            radial-gradient(circle at top, rgba(255, 159, 67, 0.16), transparent 36%),
            linear-gradient(145deg, #0f172a, #1e293b);
        padding: 16px;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.05),
            0 20px 45px rgba(15, 23, 42, 0.26);
    }

    .face-camera-frame {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: #111827;
    }

    .face-recognition-video {
        width: 100%;
        min-height: 360px;
        display: block;
        object-fit: cover;
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.95));
    }

    .face-camera-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(to bottom, rgba(15, 23, 42, 0.18), transparent 24%, transparent 76%, rgba(15, 23, 42, 0.24)),
            radial-gradient(circle at center, transparent 28%, rgba(15, 23, 42, 0.16) 100%);
    }

    .face-scan-guide {
        position: absolute;
        inset: 50% auto auto 50%;
        width: min(48vw, 230px);
        height: min(60vw, 285px);
        max-width: 230px;
        max-height: 285px;
        transform: translate(-50%, -50%);
        border-radius: 120px;
        border: 2px solid rgba(255, 255, 255, 0.92);
        box-shadow:
            0 0 0 9999px rgba(2, 6, 23, 0.16),
            0 0 0 12px rgba(255, 255, 255, 0.05);
    }

    .face-scan-guide::before {
        content: '';
        position: absolute;
        left: 14%;
        right: 14%;
        top: 22%;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, transparent, #ffb36b 12%, #fff 50%, #ffb36b 88%, transparent);
        box-shadow: 0 0 16px rgba(255, 179, 107, 0.85);
        animation: face-scan-sweep 2.8s ease-in-out infinite;
    }

    .face-camera-caption {
        position: absolute;
        left: 18px;
        right: 18px;
        bottom: 18px;
        display: flex;
        justify-content: center;
    }

    .face-camera-caption span {
        background: rgba(15, 23, 42, 0.72);
        color: #fff;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.01em;
        backdrop-filter: blur(8px);
    }

    #faceRecognitionStatus {
        margin-top: 16px;
        margin-bottom: 0;
        border-radius: 16px;
        border: 1px solid #e8edf5;
        background: #f8fafc;
        color: #334155;
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 500;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    #faceRecognitionMatchInfo {
        margin-top: 10px;
        color: #64748b;
        font-size: 13px;
        font-weight: 500;
    }

    #faceRecognitionModal .btn-cancel,
    #faceRecognitionModal .btn-submit {
        min-width: 138px;
        min-height: 46px;
        border-radius: 14px;
        font-weight: 700;
        border: 0;
        padding: 10px 18px;
    }

    #faceRecognitionModal .btn-cancel {
        background: #1f2a44;
        color: #fff;
        box-shadow: 0 10px 20px rgba(31, 42, 68, 0.18);
    }

    #faceRecognitionModal .btn-submit {
        background: linear-gradient(135deg, #ff9f43, #fb923c);
        color: #fff;
        box-shadow: 0 12px 24px rgba(251, 146, 60, 0.26);
    }

    #faceRecognitionModal .btn-submit:disabled,
    #faceRecognitionModal .btn-cancel:disabled {
        opacity: 0.85;
    }

    @keyframes face-scan-sweep {
        0%,
        100% {
            transform: translateY(0);
            opacity: 0.92;
        }
        50% {
            transform: translateY(145px);
            opacity: 1;
        }
    }

    @media (max-width: 767.98px) {
        #faceRecognitionModal .modal-dialog {
            max-width: calc(100vw - 18px);
            margin: 10px auto;
        }

        #faceRecognitionModal .modal-header,
        #faceRecognitionModal .modal-body,
        #faceRecognitionModal .modal-footer {
            padding-left: 16px;
            padding-right: 16px;
        }

        .face-modal-heading {
            gap: 10px;
        }

        .face-modal-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
        }

        .face-recognition-video {
            min-height: 280px;
        }

        .face-scan-guide {
            width: 180px;
            height: 220px;
        }

        .face-scan-guide::before {
            animation-duration: 2.4s;
        }

        #faceRecognitionModal .modal-footer {
            flex-direction: column-reverse;
        }

        #faceRecognitionModal .btn-cancel,
        #faceRecognitionModal .btn-submit {
            width: 100%;
        }
    }
</style>

<div class="modal fade" id="faceRecognitionModal" tabindex="-1" aria-labelledby="faceRecognitionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div class="face-modal-heading">
                    <div class="face-modal-icon">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <div>
                        <h5 class="face-modal-title" id="faceRecognitionModalLabel">Face Recognition</h5>
                        <p class="face-modal-subtitle" id="faceRecognitionModalSubtitle">
                            Align your face inside the camera frame.
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="face-modal-tips">
                    <span class="face-tip-chip">Good lighting</span>
                    <span class="face-tip-chip">Look straight ahead</span>
                    <span class="face-tip-chip">Keep one face in frame</span>
                </div>

                <div class="face-camera-shell">
                    <div class="face-camera-frame">
                        <video id="faceRecognitionVideo" class="face-recognition-video" autoplay muted playsinline></video>
                        <canvas id="faceRecognitionCanvas" class="d-none"></canvas>
                        <div class="face-camera-overlay"></div>
                        <div class="face-scan-guide"></div>
                        <div class="face-camera-caption">
                            <span>Center your face inside the oval</span>
                        </div>
                    </div>
                </div>

                <div class="alert" id="faceRecognitionStatus">
                    Camera is getting ready.
                </div>
                <div class="small d-none" id="faceRecognitionMatchInfo"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-submit" id="faceRecognitionCaptureBtn">Capture Face</button>
            </div>
        </div>
    </div>
</div>
