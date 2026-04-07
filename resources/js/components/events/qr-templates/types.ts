export type QrTemplateContent = {
    subtitle: string;
    title: string;
    slogan: string;
    message: string;
    eventTitle: string;
};

export type QrTemplateFonts = {
    stylesheetHref: string;
    headingFamily: string;
    bodyFamily: string;
};

export type QrTemplateProps = QrTemplateContent & {
    qrDataUrl: string;
    previewAlt: string;
    fonts: QrTemplateFonts;
};
