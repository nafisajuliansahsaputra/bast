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
                d="M7.75 3.75H14.25L18.25 7.75V19.25C18.25 19.8023 17.8023 20.25 17.25 20.25H7.75C7.19772 20.25 6.75 19.8023 6.75 19.25V4.75C6.75 4.19772 7.19772 3.75 7.75 3.75Z"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <path
                d="M14.25 3.75V7.75H18.25"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <path
                d="M9.25 13L11.25 15L15.5 10.75"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
