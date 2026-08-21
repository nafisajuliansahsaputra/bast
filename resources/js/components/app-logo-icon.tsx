import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M7.25 3.5H13.75L17.25 7V19.25C17.25 19.9404 16.6904 20.5 16 20.5H7.25C6.55964 20.5 6 19.9404 6 19.25V4.75C6 4.05964 6.55964 3.5 7.25 3.5Z"
                stroke="currentColor"
                strokeWidth="1.55"
                strokeLinecap="round"
                strokeLinejoin="round"
            />

            <path
                d="M13.75 3.5V7H17.25"
                stroke="currentColor"
                strokeWidth="1.55"
                strokeLinecap="round"
                strokeLinejoin="round"
            />

            <path
                d="M8.75 10.25H14.75"
                stroke="currentColor"
                strokeWidth="1.55"
                strokeLinecap="round"
            />

            <path
                d="M12.75 8.25L14.75 10.25L12.75 12.25"
                stroke="currentColor"
                strokeWidth="1.55"
                strokeLinecap="round"
                strokeLinejoin="round"
            />

            <path
                d="M14.5 14H8.5"
                stroke="currentColor"
                strokeWidth="1.55"
                strokeLinecap="round"
            />

            <path
                d="M10.5 12L8.5 14L10.5 16"
                stroke="currentColor"
                strokeWidth="1.55"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
