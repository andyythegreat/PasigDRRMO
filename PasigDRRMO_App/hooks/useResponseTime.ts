import { useEffect, useState } from 'react';

const useResponseTime = (startDateTime) => {
    const [responseTime, setResponseTime] = useState('00:00:00');

    useEffect(() => {
        const startTime = new Date(startDateTime);

        const calculateResponseTime = () => {
            const now = new Date();
            const differenceInMillis = now - startTime;

            if (differenceInMillis < 0) return;

            const seconds = Math.floor((differenceInMillis / 1000) % 60);
            const minutes = Math.floor((differenceInMillis / (1000 * 60)) % 60);
            const hours = Math.floor(differenceInMillis / (1000 * 60 * 60));

            setResponseTime(
                `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
            );
        };

        const interval = setInterval(calculateResponseTime, 1000);

        return () => clearInterval(interval);
    }, [startDateTime]);

    return responseTime;
};

export default useResponseTime;
