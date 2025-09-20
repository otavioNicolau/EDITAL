(function () {
    const parseVideos = (value) => {
        try {
            const parsed = JSON.parse(value);
            if (!Array.isArray(parsed)) {
                return [];
            }

            return parsed.map((video) => ({
                title: video.title || 'Vídeo',
                resolutions: Array.isArray(video.resolutions)
                    ? video.resolutions.filter((item) => item && item.url)
                    : [],
            })).filter((video) => video.resolutions.length > 0);
        } catch (error) {
            return [];
        }
    };

    const applySource = (player, videoElement, url, quality) => {
        if (!url) {
            return;
        }

        const currentTime = videoElement.currentTime || 0;
        const wasPlaying = !videoElement.paused && !videoElement.ended;

        try {
            videoElement.pause();
        } catch (error) {}

        try {
            videoElement.removeAttribute('src');
            videoElement.load();
        } catch (error) {}

        videoElement.src = url;
        videoElement.load();
        player.dataset.videoUrl = url;
        player.dataset.activeQuality = quality || '';

        videoElement.addEventListener('loadedmetadata', function handleLoaded() {
            videoElement.removeEventListener('loadedmetadata', handleLoaded);
            try {
                videoElement.currentTime = currentTime;
                if (wasPlaying) {
                    videoElement.play().catch(() => {});
                }
            } catch (error) {
                console.warn('Não foi possível restaurar o ponto do vídeo.', error);
            }
        });

        const downloadButton = player.querySelector('[data-download-current]');
        if (downloadButton) {
            downloadButton.href = url;
            downloadButton.textContent = `Baixar vídeo (${(quality || 'link').toUpperCase()})`;
        }

        const abrirVlc = player.querySelector('[data-action="abrir-vlc"]');
        if (abrirVlc) {
            abrirVlc.href = `vlc://${url}`;
        }
    };

    const copyToClipboard = async (value) => {
        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(value);
                return 'success';
            }
        } catch (error) {
            // ignore and fallback
        }

        const fallback = window.prompt('Copie o link:', value);
        return fallback !== null ? 'prompt' : 'fail';
    };

    const sanitizeFilename = (value) => {
        const base = (value || 'playlist').trim() || 'playlist';
        const normalized = typeof base.normalize === 'function' ? base.normalize('NFD') : base;

        const sanitized = normalized
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\-]+/gi, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '')
            .toLowerCase();

        return sanitized || 'playlist';
    };

    const buildPlaylist = (videos) => {
        const lines = ['#EXTM3U'];

        videos.forEach((video) => {
            const primary = video.resolutions && video.resolutions[0];
            if (!primary || !primary.url) {
                return;
            }

            lines.push(`#EXTINF:-1,${video.title || 'Vídeo'}`);
            lines.push(primary.url);
        });

        return `${lines.join('\n')}\n`;
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.video-player').forEach((player) => {
            const videoElement = player.querySelector('video');
            const videos = parseVideos(player.dataset.videos || '[]');
            const resolutionContainer = player.querySelector('[data-resolution-switch]');
            const videoSwitchContainer = player.querySelector('[data-video-switch]');
            const feedbackEl = player.querySelector('.feedback-message');

            const showFeedback = (message, type) => {
                if (!feedbackEl) {
                    return;
                }

                const typeClass = `alert-${type || 'info'}`;
                feedbackEl.textContent = message;
                feedbackEl.classList.remove('d-none', 'fade-out', 'alert-info', 'alert-success', 'alert-danger');
                feedbackEl.classList.add(typeClass);

                window.clearTimeout(player._feedbackTimeout);
                player._feedbackTimeout = window.setTimeout(() => {
                    feedbackEl.classList.add('fade-out');
                    window.setTimeout(() => {
                        feedbackEl.classList.add('d-none');
                        feedbackEl.classList.remove('fade-out', typeClass);
                    }, 400);
                }, 3500);
            };

            player.querySelectorAll('[data-action="copiar-vlc-item"]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.dataset.url;
                    if (!url) {
                        showFeedback('Link indisponível para cópia.', 'danger');
                        return;
                    }

                    const result = await copyToClipboard(`vlc://${url}`);
                    if (result === 'success') {
                        showFeedback('Link do vídeo copiado! Abra o VLC e use Ctrl+V.', 'success');
                    } else if (result === 'prompt') {
                        showFeedback('Link disponível via prompt. Copie e cole no VLC.', 'info');
                    } else {
                        showFeedback('Não foi possível copiar o link.', 'danger');
                    }
                });
            });

            const playlistCompletaButton = player.querySelector('[data-action="baixar-playlist-completa"]');
            if (playlistCompletaButton) {
                playlistCompletaButton.addEventListener('click', () => {
                    if (!videos.length) {
                        showFeedback('Playlist completa indisponível. Nenhum vídeo encontrado.', 'danger');
                        return;
                    }

                    const conteudo = buildPlaylist(videos);
                    const blob = new Blob([conteudo], { type: 'audio/x-mpegurl' });
                    const url = URL.createObjectURL(blob);
                    const anchor = document.createElement('a');
                    anchor.href = url;
                    anchor.download = `${sanitizeFilename(player.dataset.titulo || 'playlist')}_playlist-completa.m3u`;
                    document.body.appendChild(anchor);
                    anchor.click();
                    document.body.removeChild(anchor);
                    URL.revokeObjectURL(url);
                    showFeedback('Playlist completa gerada com sucesso.', 'success');
                });
            }

            const singlePlaylistButton = player.querySelector('[data-action="baixar-playlist"]');
            if (singlePlaylistButton) {
                singlePlaylistButton.addEventListener('click', () => {
                    const videoIndex = Number(player.dataset.videoIndex || 0);
                    const activeVideo = videos[videoIndex];
                    const currentUrl = player.dataset.videoUrl || (videoElement ? videoElement.currentSrc : '');

                    if (!activeVideo || !currentUrl) {
                        showFeedback('Playlist não pôde ser gerada. Sem URL do vídeo.', 'danger');
                        return;
                    }

                    const conteudo = ['#EXTM3U', `#EXTINF:-1,${activeVideo.title || 'Vídeo'}`, currentUrl, ''].join('\n');
                    const blob = new Blob([conteudo], { type: 'audio/x-mpegurl' });
                    const url = URL.createObjectURL(blob);
                    const anchor = document.createElement('a');
                    anchor.href = url;
                    anchor.download = `${sanitizeFilename(activeVideo.title || 'video')}.m3u`;
                    document.body.appendChild(anchor);
                    anchor.click();
                    document.body.removeChild(anchor);
                    URL.revokeObjectURL(url);
                    showFeedback('Playlist gerada com sucesso.', 'success');
                });
            }

            if (!videoElement || !resolutionContainer || !videos.length) {
                return;
            }

            const buildResolutionButtons = (resolutions, activeIndex = 0) => {
                const markup = resolutions.map((resolution, index) => {
                    const classes = ['btn', 'btn-sm', 'me-2'];
                    if (index === activeIndex) {
                        classes.push('btn-primary', 'text-white');
                    } else {
                        classes.push('btn-outline-primary');
                    }

                    return `<button type="button" class="${classes.join(' ')}" data-quality="${resolution.label}" data-url="${resolution.url}">${resolution.label}</button>`;
                }).join('');

                resolutionContainer.innerHTML = markup;

                resolutionContainer.querySelectorAll('button[data-quality][data-url]').forEach((button, index) => {
                    button.addEventListener('click', () => {
                        resolutionContainer.querySelectorAll('button').forEach((btn) => {
                            btn.classList.remove('btn-primary', 'text-white');
                            btn.classList.add('btn-outline-primary');
                        });

                        button.classList.remove('btn-outline-primary');
                        button.classList.add('btn-primary', 'text-white');
                        applySource(player, videoElement, button.dataset.url, button.dataset.quality);
                    });
                });

                const firstButton = resolutionContainer.querySelector('button.btn-primary');
                if (firstButton) {
                    applySource(player, videoElement, firstButton.dataset.url, firstButton.dataset.quality);
                }
            };

            const selectVideo = (index) => {
                const video = videos[index];
                if (!video || video.resolutions.length === 0) {
                    return;
                }

                player.dataset.videoIndex = String(index);
                player.dataset.titulo = video.title || 'Vídeo';

                if (videoSwitchContainer) {
                    videoSwitchContainer.querySelectorAll('button[data-video-index]').forEach((button) => {
                        if (Number(button.dataset.videoIndex) === index) {
                            button.classList.remove('btn-outline-primary');
                            button.classList.add('btn-primary', 'text-white');
                        } else {
                            button.classList.remove('btn-primary', 'text-white');
                            button.classList.add('btn-outline-primary');
                        }
                    });
                }

                buildResolutionButtons(video.resolutions, 0);
            };

            if (videoSwitchContainer) {
                videoSwitchContainer.querySelectorAll('button[data-video-index]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const index = Number(button.dataset.videoIndex);
                        selectVideo(index);
                    });
                });
            }

            selectVideo(Number(player.dataset.videoIndex || 0));
        });
    });
})();
