import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const players = document.querySelectorAll('.video-player');

    players.forEach((player) => {
        const titulo = player.dataset.titulo || 'Aula';
        const feedbackEl = player.querySelector('.feedback-message');
        let hideTimeout;

        const parseNormalizedVideos = () => {
            try {
                const parsed = JSON.parse(player.dataset.videos || '[]');
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed
                    .filter((video) => Array.isArray(video.resolutions) && video.resolutions.some((item) => item && item.url))
                    .map((video) => ({
                        title: video.title || 'Vídeo',
                        resolutions: video.resolutions
                            .filter((item) => item && item.url)
                            .map((item) => ({
                                label: item.label || 'LINK',
                                url: item.url,
                            })),
                    }));
            } catch (error) {
                return [];
            }
        };

        const normalizedVideos = parseNormalizedVideos();

        const showFeedback = (message, type = 'info') => {
            if (!feedbackEl) {
                return;
            }

            feedbackEl.textContent = message;
            feedbackEl.classList.remove('d-none', 'fade-out', 'alert-info', 'alert-success', 'alert-danger');
            feedbackEl.classList.add(`alert-${type}`);

            if (hideTimeout) {
                clearTimeout(hideTimeout);
            }

            hideTimeout = window.setTimeout(() => {
                feedbackEl.classList.add('fade-out');
                window.setTimeout(() => {
                    feedbackEl.classList.add('d-none');
                    feedbackEl.classList.remove('fade-out');
                }, 400);
            }, 3500);
        };

        const copyButton = player.querySelector('[data-action="copiar-vlc"]');
        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                const currentUrl = player.dataset.videoUrl || player.querySelector('video')?.currentSrc || '';
                const vlcLink = `vlc://${currentUrl}`;

                if (!currentUrl) {
                    showFeedback('Link indisponível para cópia.', 'danger');
                    return;
                }

                try {
                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(vlcLink);
                        showFeedback('Link copiado! Abra o VLC e use Ctrl+V.', 'success');
                    } else {
                        throw new Error('Clipboard API indisponível');
                    }
                } catch (error) {
                    const fallback = window.prompt('Copie o link para o VLC:', vlcLink);
                    if (fallback !== null) {
                        showFeedback('Link disponível via prompt.', 'info');
                    }
                }
            });
        }

        const copyToClipboard = async (value) => {
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(value);
                    return 'success';
                }
            } catch (error) {
                // ignore and fallback
            }

            const fallback = window.prompt('Copie o link:', value);
            return fallback !== null ? 'prompt' : 'fail';
        };

        player.querySelectorAll('[data-action="copiar-vlc-item"]').forEach((button) => {
            button.addEventListener('click', async () => {
                const url = button.dataset.url;

                if (!url) {
                    showFeedback('Link indisponível para cópia.', 'danger');
                    return;
                }

                const copied = await copyToClipboard(`vlc://${url}`);
                if (copied === 'success') {
                    showFeedback('Link do vídeo copiado! Abra o VLC e use Ctrl+V.', 'success');
                } else if (copied === 'prompt') {
                    showFeedback('Link disponível via prompt. Copie e cole no VLC.', 'info');
                } else {
                    showFeedback('Não foi possível copiar o link.', 'danger');
                }
            });
        });

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
                const primary = video.resolutions?.[0];
                if (!primary?.url) {
                    return;
                }

                lines.push(`#EXTINF:-1,${video.title || 'Vídeo'}`);
                lines.push(primary.url);
            });

            return lines.join('\n');
        };

        const playlistCompletaButton = player.querySelector('[data-action="baixar-playlist-completa"]');
        if (playlistCompletaButton) {
            playlistCompletaButton.addEventListener('click', () => {
                if (!normalizedVideos.length) {
                    showFeedback('Playlist completa indisponível. Nenhum vídeo encontrado.', 'danger');
                    return;
                }

                const conteudo = buildPlaylist(normalizedVideos);
                const blob = new Blob([conteudo], { type: 'audio/x-mpegurl' });
                const url = URL.createObjectURL(blob);
                const anchor = document.createElement('a');
                anchor.href = url;
                anchor.download = `${sanitizeFilename(titulo)}_playlist-completa.m3u`;
                document.body.appendChild(anchor);
                anchor.click();
                document.body.removeChild(anchor);
                URL.revokeObjectURL(url);
                showFeedback('Playlist completa gerada com sucesso.', 'success');
            });
        }

        const playlistButton = player.querySelector('[data-action="baixar-playlist"]');
        if (playlistButton) {
            playlistButton.addEventListener('click', () => {
                const currentUrl = player.dataset.videoUrl || player.querySelector('video')?.currentSrc || '';

                if (!currentUrl) {
                    showFeedback('Playlist não pôde ser gerada. Sem URL do vídeo.', 'danger');
                    return;
                }

                const conteudo = `#EXTM3U\n#EXTINF:-1,${titulo}\n${currentUrl}\n`;
                const blob = new Blob([conteudo], { type: 'audio/x-mpegurl' });
                const url = URL.createObjectURL(blob);
                const anchor = document.createElement('a');
                anchor.href = url;
                anchor.download = `${titulo.replace(/[^a-z0-9\-]+/gi, '_').toLowerCase()}.m3u`;
                document.body.appendChild(anchor);
                anchor.click();
                document.body.removeChild(anchor);
                URL.revokeObjectURL(url);
                showFeedback('Playlist gerada com sucesso.', 'success');
            });
        }
    });
});
