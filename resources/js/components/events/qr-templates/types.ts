export type QrTemplateContent = {
    subtitle: string;
    title: string;
    slogan: string;
    message: string;
    eventTitle: string;
};

export type QrTemplateProps = QrTemplateContent & {
    qrDataUrl: string;
    previewAlt: string;
};
